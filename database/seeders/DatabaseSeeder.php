<?php

namespace Database\Seeders;

use App\Models\CentralUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        CentralUser::query()->firstOrCreate(
            ['email' => 'admin@tenant.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
            ]
        );
    }
}
