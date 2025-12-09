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
        Schema::create('project_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('klien_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->enum('type', ['construction', 'renovation', 'supply', 'contractor', 'other'])->default('other');
            $table->text('description');
            $table->text('location');
            $table->decimal('expected_budget', 15, 2)->nullable();
            $table->string('expected_timeline')->nullable();
            $table->enum('status', ['pending', 'quoted', 'negotiating', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_requests');
    }
};
