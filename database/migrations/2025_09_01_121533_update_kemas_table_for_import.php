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
        Schema::table('kemas', function (Blueprint $table) {
            // Add columns for Excel import data
            $table->string('brand')->nullable()->after('employee_id');
            $table->string('lokasi')->nullable()->after('brand');
            $table->string('cell')->nullable()->after('lokasi');
            $table->string('no_id')->nullable()->after('cell');
            $table->string('set')->nullable()->after('no_id');
            
            // Quality inspection fields
            $table->integer('fw_scratched')->default(0)->after('set');
            $table->integer('fw_tear')->default(0)->after('fw_scratched');
            $table->integer('fw_smeared')->default(0)->after('fw_tear');
            $table->integer('fw_seam_open')->default(0)->after('fw_smeared');
            $table->integer('fw_alignment')->default(0)->after('fw_seam_open');
            $table->integer('fw_improper_fold')->default(0)->after('fw_alignment');
            $table->integer('fw_wrinkled')->default(0)->after('fw_improper_fold');
            $table->integer('fw_crushed')->default(0)->after('fw_wrinkled');
            $table->integer('vfi_all')->default(0)->after('fw_crushed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kemas', function (Blueprint $table) {
            $table->dropColumn([
                'brand', 'lokasi', 'cell', 'no_id', 'set',
                'fw_scratched', 'fw_tear', 'fw_smeared', 'fw_seam_open',
                'fw_alignment', 'fw_improper_fold', 'fw_wrinkled', 'fw_crushed', 'vfi_all'
            ]);
        });
    }
};
