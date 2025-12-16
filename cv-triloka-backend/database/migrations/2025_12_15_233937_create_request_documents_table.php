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
        Schema::create('request_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_request_id');
            $table->string('file_path');
            $table->string('document_type'); // 'drawing', 'photo', 'specification', 'other'
            $table->text('description')->nullable();
            $table->string('verification_status')->default('pending'); // 'pending', 'verified', 'rejected'
            $table->text('verification_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamps();
            
            // Foreign keys (without onDelete cascade to avoid migration errors)
            $table->index('project_request_id');
            $table->index('verified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_documents');
    }
};
