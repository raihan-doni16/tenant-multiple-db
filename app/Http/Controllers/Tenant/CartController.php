<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\ShoppingCartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $response = DB::transaction(function () use ($cart, $data) {
            $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);

            if (! $product->is_active) {
                return response()->json([
                    'message' => 'Product is not available.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
            $currentQuantity = $item->exists ? $item->quantity : 0;
            $difference = (int) $data['quantity'];

            if ($product->stock < $difference) {
                return response()->json([
                    'message' => 'Quantity exceeds available stock.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $product->decrement('stock', $difference);

            $item->quantity = $currentQuantity + $difference;
            $item->unit_price = $product->price;
            $item->save();

            $cart->load('items.product');

            return response()->json($this->formatCart($cart), Response::HTTP_CREATED);
        });

        return $response;
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

        $response = DB::transaction(function () use ($shoppingCartItem, $data, $cart) {
            $shoppingCartItem->loadMissing('product');
            $product = Product::query()->lockForUpdate()->findOrFail($shoppingCartItem->product_id);

            $newQuantity = (int) $data['quantity'];

            if ($newQuantity === 0) {
                $product->increment('stock', $shoppingCartItem->quantity);
                $shoppingCartItem->delete();
                $cart->load('items.product');

                return response()->json($this->formatCart($cart));
            }

            $difference = $newQuantity - $shoppingCartItem->quantity;

            if ($difference > 0) {
                if ($product->stock < $difference) {
                    return response()->json([
                        'message' => 'Quantity exceeds available stock.',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $product->decrement('stock', $difference);
            } elseif ($difference < 0) {
                $product->increment('stock', abs($difference));
            }

            $shoppingCartItem->update(['quantity' => $newQuantity]);
            $cart->load('items.product');

            return response()->json($this->formatCart($cart));
        });

        return $response;
    }

    public function removeItem(Request $request, ShoppingCartItem $shoppingCartItem): JsonResponse
    {
        $cart = $this->resolveCart($request);

        $shoppingCartItem->loadMissing('product');

        if ($shoppingCartItem->shopping_cart_id !== $cart->id) {
            abort(Response::HTTP_FORBIDDEN, 'Item does not belong to your cart.');
        }

        DB::transaction(function () use ($shoppingCartItem, $cart) {
            $product = Product::query()->lockForUpdate()->findOrFail($shoppingCartItem->product_id);
            $product->increment('stock', $shoppingCartItem->quantity);
            $shoppingCartItem->delete();
            $cart->load('items.product');
        });

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
