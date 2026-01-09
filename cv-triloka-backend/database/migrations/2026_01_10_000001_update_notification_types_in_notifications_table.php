<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Because SQLite doesn't support modifying ENUM, and for MySQL we want to be safe, 
        // we'll use a direct DB statement if MySQL or just leave it for SQLite (which uses text for enum)
        
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('payment_reminder', 'overdue_alert', 'stock_alert', 'payment_received', 'new_negotiation', 'negotiation_accepted', 'negotiation_rejected')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('payment_reminder', 'overdue_alert', 'stock_alert', 'payment_received')");
        }
    }
};
