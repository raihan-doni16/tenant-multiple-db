<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

use Illuminate\Support\Facades\DB;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanupTenantDatabases();
        $this->ensureCentralDatabaseExists();
    }

    protected function tearDown(): void
    {
        $this->disconnectTenantConnections();
        $this->cleanupTenantDatabases();
        parent::tearDown();
    }

    protected function cleanupTenantDatabases(): void
    {
        if (! app()->environment('testing')) {
            return;
        }

        $tenantDatabases = glob(database_path('tenant_*'));

        if ($tenantDatabases === false) {
            return;
        }

        foreach ($tenantDatabases as $databaseFile) {
            @unlink($databaseFile);
        }
    }

    protected function ensureCentralDatabaseExists(): void
    {
        if (! app()->environment('testing')) {
            return;
        }

        $connection = config('database.connections.central');

        if (($connection['driver'] ?? null) !== 'sqlite') {
            return;
        }

        $databasePath = $connection['database'] ?? null;

        if (! $databasePath || $databasePath === ':memory:') {
            return;
        }

        if (! file_exists($databasePath)) {
            touch($databasePath);
        }
    }

    protected function disconnectTenantConnections(): void
    {
        $connections = ['tenant', 'tenant_template', config('database.default'), 'central'];

        foreach (array_filter(array_unique($connections)) as $connection) {
            try {
                DB::disconnect($connection);
            } catch (Throwable) {
                // Ignore if the connection is not configured during the test run.
            }
        }
    }
}
