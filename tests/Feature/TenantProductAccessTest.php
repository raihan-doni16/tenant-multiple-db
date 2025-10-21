<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantProductAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Tenancy migrations are run via tenant creation.
        Tenant::create([
            'id' => 'test-tenant',
            'display_name' => 'Test Tenant',
        ]);
    }

    public function test_owner_can_list_products(): void
    {
        $tenant = Tenant::find('test-tenant');

        $tenant->run(function () {
            User::create([
                'name' => 'Owner',
                'email' => 'owner@example.com',
                'password' => 'password',
                'metadata' => ['role' => 'owner'],
            ]);
        });

        $tenant->run(function () use ($tenant) {
            $owner = User::firstWhere('email', 'owner@example.com');
            Sanctum::actingAs($owner);

            $response = $this->getJson("/api/{$tenant->id}/products");
            $response->assertOk();
        });
    }
}
