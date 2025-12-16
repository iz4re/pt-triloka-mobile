<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('va_number')->nullable()->after('invoice_number');
            $table->string('va_bank')->default('BCA')->after('va_number');
            $table->timestamp('va_expires_at')->nullable()->after('va_bank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['va_number', 'va_bank', 'va_expires_at']);
        });
    }
};
