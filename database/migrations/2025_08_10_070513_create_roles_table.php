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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'superadmin', 'admin', 'viewer'
            $table->string('display_name'); // e.g., 'Super Administrator'
            $table->string('description')->nullable(); // Role description
            $table->boolean('is_active')->default(true);
            $table->integer('level')->default(0); // Role hierarchy level (higher = more access)
            $table->timestamps();
            
            $table->index(['is_active', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
