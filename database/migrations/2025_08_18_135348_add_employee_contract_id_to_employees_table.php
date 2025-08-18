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
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('employee_contract_id')->nullable()->after('location_id')->references('id')->on('employee_contracts')->onDelete('set null');
            $table->index('employee_contract_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['employee_contract_id']);
            $table->dropIndex(['employee_contract_id']);
            $table->dropColumn('employee_contract_id');
        });
    }
};
