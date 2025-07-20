<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kemas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->references('id')->on('brands');
            $table->foreignId('location_id')->references('id')->on('locations');
            $table->string('code');
            $table->integer('set')->default(0);
            $table->unsignedInteger('fw_scratched_marked')->default(0);
            $table->unsignedInteger('fw_tear_slit_hole')->default(0);
            $table->unsignedInteger('fw_smeared_dirty')->default(0);
            $table->unsignedInteger('fw_seam_open')->default(0);
            $table->unsignedInteger('fw_alignment')->default(0);
            $table->unsignedInteger('fw_improper_fold')->default(0);
            $table->unsignedInteger('fw_wrinkled_pleated')->default(0);
            $table->unsignedInteger('fw_crushed_dented')->default(0);
            $table->unsignedInteger('vfi_all')->default(0);
            $table->softDeletes();
            $table->timestamps();
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
