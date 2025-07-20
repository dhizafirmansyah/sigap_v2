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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('marital_status', ['single', 'married','divorced','other'])->nullable();
            $table->enum('education', ['sd', 'smp','sma','smk','d3','d4','s1','s2','s3'])->nullable();
            $table->text('address')->nullable();
            $table->string('status')->nullable();
            $table->boolean('is_disability')->default(false);
            $table->string('phone')->nullable();
            $table->foreignId('pkwt_id')->references('id')->on('pkwts');
            $table->foreignId('location_id')->references('id')->on('locations');
            $table->foreignId('employee_contract_id')->references('id')->on('employee_contracts');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
