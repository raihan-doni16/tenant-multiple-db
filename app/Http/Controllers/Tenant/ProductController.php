<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $products = Product::query()
            ->when($request->has('active') && $request->boolean('active'), fn ($query) => $query->active())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('name', 'ilike', "%{$search}%")
                        ->orWhere('sku', 'ilike', "%{$search}%")
                        ->orWhere('slug', 'ilike', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $this->ensureOwner($request);

        $data = $request->validate([
            'sku' => ['nullable', 'string', 'max:64', 'alpha_dash', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'attributes' => ['nullable', 'array'],
        ]);

        if (empty($data['sku'])) {
            $data['sku'] = $this->generateSku($data['name']);
        }

        $product = Product::create($data);

        return response()->json($product, 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $this->ensureOwner($request);

        $data = $request->validate([
            'sku' => ['nullable', 'string', 'max:64', 'alpha_dash', Rule::unique('products', 'sku')->ignore($product->id)],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product->id)],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'attributes' => ['nullable', 'array'],
        ]);

        if (array_key_exists('sku', $data) && empty($data['sku'])) {
            $name = $data['name'] ?? $product->name;

            if ($name) {
                $data['sku'] = $this->generateSku($name);
            }
        }

        $product->fill($data)->save();

        return response()->json($product->refresh());
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $this->ensureOwner($request);

        $product->delete();

       return response()->json([
            'data' => $product,
        ], 201);
    }

    protected function ensureOwner(Request $request): void
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('owner')) {
            abort(Response::HTTP_FORBIDDEN, 'Only store owners can manage products.');
        }
    }

    protected function generateSku(string $name): string
    {
        $base = Str::of($name)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '-')
            ->trim('-')
            ->substr(0, 12)
            ->trim('-');

        if ($base->isEmpty()) {
            $base = Str::upper(Str::random(6));
        }

        $baseSku = (string) $base;
        $sku = $baseSku;

        while (Product::where('sku', $sku)->exists()) {
            $suffix = Str::upper(Str::random(4));
            $sku = Str::of($baseSku)
                ->append('-')
                ->append($suffix)
                ->substr(0, 64);
        }

        return $sku;
    }
}
