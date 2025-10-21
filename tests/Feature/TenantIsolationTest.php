<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\ShoppingCartItem;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantOne;

    protected Tenant $tenantTwo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantOne = Tenant::create([
            'id' => 'tenant-one',
            'display_name' => 'Tenant One',
        ]);

        $this->tenantTwo = Tenant::create([
            'id' => 'tenant-two',
            'display_name' => 'Tenant Two',
        ]);
    }

    public function test_product_list_isolated_per_tenant(): void
    {
        $productOne = $this->tenantOne->run(function () {
            $product = Product::create([
                'name' => 'Tenant One Product',
                'price' => 10000,
                'stock' => 5,
                'is_active' => true,
            ]);

            return $product->only(['id', 'name']);
        });

        $productTwo = $this->tenantTwo->run(function () {
            $product = Product::create([
                'name' => 'Tenant Two Product',
                'price' => 12000,
                'stock' => 3,
                'is_active' => true,
            ]);

            return $product->only(['id', 'name']);
        });

        $responseOne = $this->getJson("/api/{$this->tenantOne->id}/products");
        $responseTwo = $this->getJson("/api/{$this->tenantTwo->id}/products");

        $responseOne
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'name' => $productOne['name'],
            ])
            ->assertJsonMissing([
                'name' => $productTwo['name'],
            ]);

        $responseTwo
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'name' => $productTwo['name'],
            ])
            ->assertJsonMissing([
                'name' => $productOne['name'],
            ]);
    }

    public function test_cart_state_isolated_per_tenant(): void
    {
        $tenantOneData = $this->tenantOne->run(function () {
            $product = Product::create([
                'name' => 'Tenant One Product',
                'price' => 15000,
                'stock' => 10,
                'is_active' => true,
            ]);

            $user = User::create([
                'name' => 'Tenant One Customer',
                'email' => 'tenant1@example.com',
                'password' => 'secret-password',
                'metadata' => ['role' => 'customer'],
            ]);

            return [
                'product_id' => $product->id,
                'user_id' => $user->id,
            ];
        });

        $tenantTwoData = $this->tenantTwo->run(function () {
            $product = Product::create([
                'name' => 'Tenant Two Product',
                'price' => 22000,
                'stock' => 8,
                'is_active' => true,
            ]);

            $user = User::create([
                'name' => 'Tenant Two Customer',
                'email' => 'tenant2@example.com',
                'password' => 'secret-password',
                'metadata' => ['role' => 'customer'],
            ]);

            return [
                'product_id' => $product->id,
                'user_id' => $user->id,
            ];
        });

        $this->tenantOne->run(function () use ($tenantOneData) {
            $user = User::findOrFail($tenantOneData['user_id']);
            Sanctum::actingAs($user);

            $response = $this->postJson("/api/{$this->tenantOne->id}/cart/items", [
                'product_id' => $tenantOneData['product_id'],
                'quantity' => 2,
            ]);

            $response->assertCreated();

            $this->assertSame(1, ShoppingCart::count());
            $this->assertSame(2, (int) ShoppingCartItem::sum('quantity'));
        });

        $this->tenantTwo->run(function () use ($tenantTwoData) {
            $user = User::findOrFail($tenantTwoData['user_id']);
            Sanctum::actingAs($user);

            $response = $this->postJson("/api/{$this->tenantTwo->id}/cart/items", [
                'product_id' => $tenantTwoData['product_id'],
                'quantity' => 1,
            ]);

            $response->assertCreated();

            $this->assertSame(1, ShoppingCart::count());
            $this->assertSame(1, (int) ShoppingCartItem::sum('quantity'));
        });

        $tenantOneSummary = $this->tenantOne->run(fn () => [
            'carts' => ShoppingCart::count(),
            'items' => (int) ShoppingCartItem::sum('quantity'),
        ]);

        $tenantTwoSummary = $this->tenantTwo->run(fn () => [
            'carts' => ShoppingCart::count(),
            'items' => (int) ShoppingCartItem::sum('quantity'),
        ]);

        $this->assertSame(['carts' => 1, 'items' => 2], $tenantOneSummary);
        $this->assertSame(['carts' => 1, 'items' => 1], $tenantTwoSummary);
    }
}
