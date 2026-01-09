<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = User::where('email', 'admin@triloka.com')->first();

if ($admin) {
    $admin->password = Hash::make('admin123');
    $admin->save();
    echo "✅ Password reset successful!\n";
    echo "Email: admin@triloka.com\n";
    echo "Password: admin123\n";
} else {
    echo "❌ Admin user not found. Creating new admin...\n";
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@triloka.com',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
        'is_active' => true,
    ]);
    echo "✅ Admin user created!\n";
    echo "Email: admin@triloka.com\n";
    echo "Password: admin123\n";
}
