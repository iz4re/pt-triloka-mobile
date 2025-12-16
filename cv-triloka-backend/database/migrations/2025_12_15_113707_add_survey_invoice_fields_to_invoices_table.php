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
            $table->enum('invoice_type', ['survey', 'project'])->default('project')->after('status');
            $table->unsignedBigInteger('parent_invoice_id')->nullable()->after('invoice_type');
            $table->boolean('is_survey_fee_applied')->default(false)->after('parent_invoice_id');
            
            // Add index for parent_invoice_id
            $table->index('parent_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['parent_invoice_id']);
            $table->dropColumn(['invoice_type', 'parent_invoice_id', 'is_survey_fee_applied']);
        });
    }
};
