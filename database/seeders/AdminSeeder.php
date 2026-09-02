<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's default administrator account.
     */
    public function run(): void
    {
        Admin::query()->updateOrCreate(
            ['email' => 'admin@cshield.test'],
            [
                'name' => 'Administrator C-SHIELD',
                'password' => 'password',
            ]
        );
    }
}
