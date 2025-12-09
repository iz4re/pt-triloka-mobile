<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing test users (safe with foreign keys)
        User::whereIn('email', ['admin@triloka.com', 'mitra@example.com'])->delete();
        
        // Create test users
        User::create([
            'name' => 'Admin Triloka',
            'email' => 'admin@triloka.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Mitra Sejahtera',
            'email' => 'mitra@example.com',
            'password' => Hash::make('password'),
            'role' => 'klien',
            'is_active' => true,
        ]);

        $this->command->info('Test users seeded successfully!');
    }
}
