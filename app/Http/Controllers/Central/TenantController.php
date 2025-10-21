<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCart;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Throwable;

class TenantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = max(1, min($perPage, 100));
        $search = trim((string) $request->query('search', ''));

        $tenants = Tenant::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'like', "%{$search}%")
                        ->orWhere('data->name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->through(function (Tenant $tenant) {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->database()->getName(),
                    'database' => $tenant->database()->getName(),
                    'created_at' => $tenant->created_at,
                    'updated_at' => $tenant->updated_at,
                ];
            });

        return response()->json($tenants);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:64', 'alpha_dash', Rule::unique('tenants', 'id')],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'email', 'max:255'],
            'admin_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $tenant = Tenant::create([
            'id' => $validated['slug'],
            'data' => [
                'name' => $validated['name'],
                'slug' => $validated['slug'],
            ],
        ]);

        try {
            $tenant->run(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['admin_name'],
                    'email' => $validated['admin_email'],
                    'password' => $validated['admin_password'],
                    'metadata' => ['role' => 'owner'],
                ]);

                ShoppingCart::firstOrCreate(['user_id' => $user->id]);
            });
        } catch (Throwable $exception) {
            Log::error('Failed to provision tenant database', [
                'tenant_id' => $tenant->id,
                'exception' => $exception->getMessage(),
            ]);

            $tenant->delete();

            throw $exception;
        }

        return response()->json([
            'id' => $tenant->id,
            'name' => $tenant->name,
            'database' => $tenant->database()->getName(),
        ], 201);
    }

    public function destroy(string $tenantId): JsonResponse
    {
        $tenant = Tenant::query()->findOrFail($tenantId);
        $tenant->delete();

       return response()->json([    
            'data' => $tenant,
        ], 201);
    }
}
