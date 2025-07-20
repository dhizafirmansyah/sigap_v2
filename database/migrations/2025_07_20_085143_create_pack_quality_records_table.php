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
        Schema::create('pack_quality_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pack_id')->references('id')->on('packs')->onDelete('cascade');
            $table->foreignId('quality_metric_id')->references('id')->on('quality_metrics')->onDelete('restrict');
            $table->unsignedInteger('count')->default(0);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['pack_id', 'quality_metric_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pack_quality_records');
    }
};
