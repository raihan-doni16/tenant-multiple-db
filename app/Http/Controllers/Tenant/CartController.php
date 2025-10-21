<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\ShoppingCartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $cart = $this->resolveCart($request);

        return response()->json($this->formatCart($cart));
    }

    public function addItem(Request $request): JsonResponse
    {
        $cart = $this->resolveCart($request);

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::query()->whereKey($data['product_id'])->firstOrFail();

        if (! $product->is_active) {
            return response()->json([
                'message' => 'Product is not available.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
        $requestedQuantity = ($item->exists ? $item->quantity : 0) + $data['quantity'];

        if ($product->stock < $requestedQuantity) {
            return response()->json([
                'message' => 'Quantity exceeds available stock.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item->quantity = $requestedQuantity;
        $item->unit_price = $product->price;
        $item->save();

        $cart->load('items.product');

        return response()->json($this->formatCart($cart), Response::HTTP_CREATED);
    }

    public function updateItem(Request $request, ShoppingCartItem $shoppingCartItem): JsonResponse
    {
        $cart = $this->resolveCart($request);

        if ($shoppingCartItem->shopping_cart_id !== $cart->id) {
            abort(Response::HTTP_FORBIDDEN, 'Item does not belong to your cart.');
        }

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $shoppingCartItem->loadMissing('product');

        if ((int) $data['quantity'] === 0) {
            $shoppingCartItem->delete();
            $cart->load('items.product');

            return response()->json($this->formatCart($cart));
        }

        if ($shoppingCartItem->product->stock < $data['quantity']) {
            return response()->json([
                'message' => 'Quantity exceeds available stock.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $shoppingCartItem->update(['quantity' => $data['quantity']]);
        $cart->load('items.product');

        return response()->json($this->formatCart($cart));
    }

    public function removeItem(Request $request, ShoppingCartItem $shoppingCartItem): JsonResponse
    {
        $cart = $this->resolveCart($request);

        $shoppingCartItem->loadMissing('product');

        if ($shoppingCartItem->shopping_cart_id !== $cart->id) {
            abort(Response::HTTP_FORBIDDEN, 'Item does not belong to your cart.');
        }

        $shoppingCartItem->delete();
        $cart->load('items.product');

        return response()->json($this->formatCart($cart));
    }

    protected function resolveCart(Request $request): ShoppingCart
    {
        return ShoppingCart::with('items.product')->firstOrCreate([
            'user_id' => $request->user()->id,
        ]);
    }

    protected function formatCart(ShoppingCart $cart): array
    {
        $cart->loadMissing('items.product');

        $items = $cart->items->map(function (ShoppingCartItem $item) {
            return [
                'id' => $item->id,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'sku' => $item->product->sku,
                    'price' => (float) $item->unit_price,
                    'is_active' => $item->product->is_active,
                ],
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => $item->subtotal(),
            ];
        });

        $subtotal = $items->sum(fn (array $item) => $item['subtotal']);

        return [
            'id' => $cart->id,
            'status' => $cart->status,
            'items' => $items,
            'subtotal' => $subtotal,
            'total_items' => $items->sum(fn (array $item) => $item['quantity']),
        ];
    }
}
