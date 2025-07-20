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
        Schema::create('kemas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->references('id')->on('brands')->onDelete('restrict');
            $table->foreignId('location_id')->references('id')->on('locations')->onDelete('restrict');
            $table->foreignId('employee_id')->nullable()->references('id')->on('employees')->onDelete('set null');
            
            $table->string('batch_code')->nullable();
            $table->integer('target_quantity')->default(0);
            $table->integer('actual_quantity')->default(0);
            
            // Production status
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('kemas_time')->nullable();
            
            // Quality metrics - normalized to separate table
            $table->text('quality_notes')->nullable();
            $table->decimal('efficiency_percentage', 5, 2)->nullable();
            
            $table->timestamps();
            
            $table->index(['brand_id', 'location_id']);
            $table->index('kemas_time');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kemas');
    }
};
