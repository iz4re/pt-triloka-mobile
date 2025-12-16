<?php

// Quick diagnostic script - run this to check quotation data
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== QUOTATION DIAGNOSTIC ===\n\n";

// 1. Check all users
echo "1. ALL USERS:\n";
$users = App\Models\User::select('id', 'name', 'email', 'role')->get();
foreach ($users as $user) {
    echo "   ID: {$user->id} | {$user->name} ({$user->email}) | Role: {$user->role}\n";
}

// 2. Check quotations with sent status
echo "\n2. SENT QUOTATIONS:\n";
$quotations = App\Models\Quotation::with(['projectRequest'])
    ->where('status', 'sent')
    ->get();

foreach ($quotations as $q) {
    echo "\n   Quotation: {$q->quotation_number}\n";
    echo "   Status: {$q->status}\n";
    echo "   Project ID: {$q->project_request_id}\n";
    
    if ($q->projectRequest) {
        echo "   Project user_id: " . ($q->projectRequest->user_id ?? 'NULL') . "\n";
        echo "   Project klien_id: " . ($q->projectRequest->klien_id ?? 'NULL') . "\n";
        echo "   Project title: {$q->projectRequest->title}\n";
        
        // Check which user this belongs to
        $userId = $q->projectRequest->user_id ?? $q->projectRequest->klien_id;
        if ($userId) {
            $owner = App\Models\User::find($userId);
            if ($owner) {
                echo "   Owner: {$owner->name} ({$owner->email})\n";
            }
        }
    } else {
        echo "   ERROR: No project request found!\n";
    }
}

echo "\n3. RECOMMENDATION:\n";
echo "   - Mobile app should login as one of the users listed above\n";
echo "   - Check if user_id or klien_id matches the logged-in user\n";
echo "   - If mismatch, quotation won't appear!\n";

echo "\n=== END DIAGNOSTIC ===\n";
