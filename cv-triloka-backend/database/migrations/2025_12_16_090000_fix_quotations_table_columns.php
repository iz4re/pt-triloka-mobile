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
        Schema::table('quotations', function (Blueprint $table) {
            // Add quotation_date column if it doesn't exist
            if (!Schema::hasColumn('quotations', 'quotation_date')) {
                $table->date('quotation_date')->after('quotation_number')->nullable();
                
                // Set existing records to created_at date
                \DB::statement('UPDATE quotations SET quotation_date = DATE(created_at) WHERE quotation_date IS NULL');
                
                // Make it not nullable after populating
                $table->date('quotation_date')->nullable(false)->change();
            }
            
            // Rename request_id to project_request_id if it exists
            if (Schema::hasColumn('quotations', 'request_id')) {
                $table->renameColumn('request_id', 'project_request_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (Schema::hasColumn('quotations', 'quotation_date')) {
                $table->dropColumn('quotation_date');
            }
            
            if (Schema::hasColumn('quotations', 'project_request_id')) {
                $table->renameColumn('project_request_id', 'request_id');
            }
        });
    }
};
