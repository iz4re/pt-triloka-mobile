<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetAdminPasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@triloka.com')->first();
        
        if ($admin) {
            $admin->password = Hash::make('admin123');
            $admin->role = 'admin'; // Make sure role is set
            $admin->save();

            $this->command->info('Admin password reset successfully!');
            $this->command->info('Email: admin@triloka.com');
            $this->command->info('Password: admin123');
        } else {
            $this->command->error('Admin user not found!');
        }
    }
}
