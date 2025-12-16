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
        Schema::table('project_requests', function (Blueprint $table) {
            // Check if user_id exists but klien_id is what we're using
            if (Schema::hasColumn('project_requests', 'user_id')) {
                // Make user_id nullable first to avoid errors
                $table->unsignedBigInteger('user_id')->nullable()->change();
                
                // If klien_id exists, copy data from klien_id to user_id
                if (Schema::hasColumn('project_requests', 'klien_id')) {
                    DB::statement('UPDATE project_requests SET user_id = klien_id WHERE user_id IS NULL');
                }
            } else {
                // If user_id doesn't exist, create it
                $table->foreignId('user_id')
                      ->after('request_number')
                      ->constrained('users')
                      ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_requests', function (Blueprint $table) {
            // Don't drop user_id as it might be needed
        });
    }
};
