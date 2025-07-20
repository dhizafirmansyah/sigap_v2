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
        Schema::create('presences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->references('id')->on('shifts')->onDelete('set null');
            
            // Check in data
            $table->timestamp('check_in')->nullable();
            $table->decimal('latitude_checkin', 10, 7)->nullable();
            $table->decimal('longitude_checkin', 10, 7)->nullable();
            $table->string('photo_checkin')->nullable();
            $table->text('notes_checkin')->nullable();
            
            // Check out data
            $table->timestamp('check_out')->nullable();
            $table->decimal('latitude_checkout', 10, 7)->nullable();
            $table->decimal('longitude_checkout', 10, 7)->nullable();
            $table->string('photo_checkout')->nullable();
            $table->text('notes_checkout')->nullable();
            
            // Calculated fields
            $table->decimal('work_hours', 5, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->enum('status', ['present', 'late', 'absent', 'holiday', 'sick', 'permission'])->default('present');
            
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['employee_id', 'check_in']);
            $table->index('check_in');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
