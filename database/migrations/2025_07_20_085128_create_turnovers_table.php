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
        Schema::create('turnovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->enum('type', ['resignation', 'termination', 'retirement', 'other']);
            $table->string('reason')->nullable();
            $table->text('detailed_reason')->nullable();
            $table->dateTime('turnover_time')->nullable();
            $table->date('effective_date'); // Tanggal efektif keluar
            $table->date('last_working_day')->nullable();
            $table->boolean('exit_interview_completed')->default(false);
            $table->json('exit_interview_data')->nullable();
            $table->foreignId('processed_by')->nullable()->references('id')->on('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['type', 'effective_date']);
            $table->index('effective_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnovers');
    }
};
