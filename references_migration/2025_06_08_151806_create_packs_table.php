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
        Schema::create('packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->references('id')->on('brands');
            $table->foreignId('location_id')->references('id')->on('locations');
            $table->string('code');
            $table->integer('set')->default(0);
            $table->unsignedInteger('visual_quality_blank_label')->default(0);
            $table->unsignedInteger('visual_quality_black_label_bl_tear_slit')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_bl_smeared_dirty')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_bl_pack_adhesion_01')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_bl_pack_adhesion_02')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_bl_pack_adhesion_03')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_bl_pack_adhesion_04')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_bl_label_edge_aligntment')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_bl_artwork_alignment')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_bl_seam_skew_01')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_bl_improper_bs_fold_05')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_bl_over_folding')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_bl_turn_back_under')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_bl_wrinkled')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_bl_inner_flaps_folding')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_ms_crushed_dented')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_ms_pack_stuck_together')->default(0);
            $table->unsignedInteger('visual_quality_blank_label_ms_lid_miter_stuck')->default(0);
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
        Schema::dropIfExists('packs');
    }
};
