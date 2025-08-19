<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

                // Seed Filament Shield (adjust class name if your package uses ShieldSeeder instead)
        // Run an artisan command from the seeder
        Artisan::call('db:seed --class=ShieldSeeder');
        $user = \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
        ]);

        $user->assignRole('super_admin');

        $staffadmin = \App\Models\Staff::factory()->create([
            'name' => 'Staff Admin',
            'email' => 'staffadmin@admin.com',
        ]);

        $staffadmin->assignRole('staff_admin');
    }
}
