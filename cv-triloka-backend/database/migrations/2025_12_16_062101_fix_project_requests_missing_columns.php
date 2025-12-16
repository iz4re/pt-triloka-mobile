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
        // Check and add missing columns to project_requests table
        Schema::table('project_requests', function (Blueprint $table) {
            // Add klien_id if missing
            if (!Schema::hasColumn('project_requests', 'klien_id')) {
                $table->foreignId('klien_id')
                      ->after('request_number')
                      ->constrained('users')
                      ->onDelete('cascade');
            }
            
            // Add request_number if missing  
            if (!Schema::hasColumn('project_requests', 'request_number')) {
                $table->string('request_number')->unique()->after('id');
            }
            
            // Add description if missing
            if (!Schema::hasColumn('project_requests', 'description')) {
                $table->text('description')->after('type');
            }
            
            // Add location if missing
            if (!Schema::hasColumn('project_requests', 'location')) {
                $table->text('location')->after('description');
            }
            
            // Add expected_budget if missing
            if (!Schema::hasColumn('project_requests', 'expected_budget')) {
                $table->decimal('expected_budget', 15, 2)->nullable()->after('location');
            }
            
            // Add expected_timeline if missing
            if (!Schema::hasColumn('project_requests', 'expected_timeline')) {
                $table->string('expected_timeline')->nullable()->after('expected_budget');
            }
            
            // Add status if missing
            if (!Schema::hasColumn('project_requests', 'status')) {
                $table->enum('status', ['pending', 'quoted', 'negotiating', 'approved', 'rejected', 'cancelled'])
                      ->default('pending')
                      ->after('expected_timeline');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_requests', function (Blueprint $table) {
            $table->dropColumn([
                'klien_id',
                'request_number',
                'description', 
                'location',
                'expected_budget',
                'expected_timeline',
                'status'
            ]);
        });
    }
};
