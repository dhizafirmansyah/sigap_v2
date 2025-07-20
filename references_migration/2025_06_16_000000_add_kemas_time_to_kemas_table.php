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
        Schema::table('kemas', function (Blueprint $table) {
            $table->timestamp('kemas_time')->nullable()->after('vfi_all');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kemas', function (Blueprint $table) {
            $table->dropColumn('kemas_time');
        });
    }
};
