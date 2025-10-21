<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->when($request->boolean('active'), fn ($query) => $query->active())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('name', 'ilike', "%{$search}%")
                        ->orWhere('sku', 'ilike', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

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
}
