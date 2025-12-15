<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        $adminExists = User::where('email', 'admin@triloka.com')->exists();
        
        if (!$adminExists) {
            User::create([
                'name' => 'Admin Triloka',
                'email' => 'admin@triloka.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '081234567890',
                'address' => 'Jakarta',
                'company_name' => 'CV Triloka',
            ]);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: admin@triloka.com');
            $this->command->info('Password: password123');
        } else {
            $this->command->info('Admin user already exists!');
        }
    }
}
