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
        Schema::create('packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->references('id')->on('brands')->onDelete('restrict');
            $table->foreignId('location_id')->references('id')->on('locations')->onDelete('restrict');
            $table->foreignId('kemas_id')->nullable()->references('id')->on('kemas')->onDelete('set null');
            $table->foreignId('employee_id')->nullable()->references('id')->on('employees')->onDelete('set null');
            
            $table->string('code');
            $table->string('batch_code')->nullable();
            $table->integer('set')->default(0);
            $table->integer('quantity_per_pack')->default(20); // Jumlah rokok per pack
            
            // Production tracking
            $table->enum('status', ['produced', 'packed', 'quality_checked', 'shipped', 'defective'])->default('produced');
            $table->timestamp('pack_time')->nullable();
            $table->timestamp('quality_check_time')->nullable();
            
            // Quality data - normalized to separate table
            $table->boolean('quality_passed')->nullable();
            $table->text('quality_notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['brand_id', 'location_id']);
            $table->index('pack_time');
            $table->index(['status', 'created_at']);
            $table->index('batch_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packs');
    }
};
