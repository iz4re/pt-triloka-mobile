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
        Schema::table('project_requests', function (Blueprint $table) {
            // Add type column if it doesn't exist
            if (!Schema::hasColumn('project_requests', 'type')) {
                $table->enum('type', ['construction', 'renovation', 'supply', 'contractor', 'other'])
                      ->default('other')
                      ->after('title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_requests', function (Blueprint $table) {
            if (Schema::hasColumn('project_requests', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
