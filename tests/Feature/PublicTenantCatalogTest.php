<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTenantCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_paginated_tenant_catalog(): void
    {
        foreach (range(1, 3) as $index) {
            Tenant::create([
                'id' => "tenant-{$index}",
                'display_name' => "Tenant {$index}",
            ]);
        }

        $response = $this->getJson('/api/tenants?per_page=2');

        $response
            ->assertOk()
            ->assertJsonPath('per_page', 2)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Tenant 1'])
            ->assertJsonFragment(['name' => 'Tenant 2']);
    }

    public function test_it_supports_searching_tenants_by_name_or_id(): void
    {
        Tenant::create([
            'id' => 'alpha-shop',
            'display_name' => 'Alpha Shop',
        ]);

        Tenant::create([
            'id' => 'beta-store',
            'display_name' => 'Beta Store',
        ]);

        $response = $this->getJson('/api/tenants?search=beta');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => 'beta-store'])
            ->assertJsonMissing(['id' => 'alpha-shop']);
    }
}
