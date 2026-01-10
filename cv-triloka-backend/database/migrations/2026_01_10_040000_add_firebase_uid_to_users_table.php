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
        if (!Schema::hasColumn('users', 'firebase_uid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('firebase_uid')->nullable()->unique()->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'firebase_uid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('firebase_uid');
            });
        }
    }
};
