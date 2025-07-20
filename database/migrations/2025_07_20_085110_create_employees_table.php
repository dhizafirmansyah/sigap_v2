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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique(); // ID karyawan unik
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'other'])->nullable();
            $table->enum('education', ['sd', 'smp', 'sma', 'diploma', 'sarjana', 'other'])->nullable();
            
            // Employment details
            $table->foreignId('pkwt_id')->nullable()->references('id')->on('pkwts')->onDelete('restrict');
            $table->foreignId('location_id')->references('id')->on('locations')->onDelete('restrict');
            // employee_contract_id akan diset setelah kontrak dibuat
            
            $table->date('hire_date');
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            
            $table->enum('status', ['active', 'inactive', 'terminated', 'resigned'])->default('active');
            $table->text('notes')->nullable();
            
            // Emergency contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['location_id', 'status']);
            $table->index('hire_date');
            $table->index(['department', 'status']);
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
