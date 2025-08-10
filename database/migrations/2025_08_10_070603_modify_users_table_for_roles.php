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
        Schema::table('users', function (Blueprint $table) {
            // Remove the old enum role column
            $table->dropColumn('role');
            // Add foreign key to roles table
            $table->foreignId('role_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_active')->default(true);
            
            $table->index(['role_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'is_active']);
            $table->enum('role', ['superadmin', 'admin', 'viewer'])->default('viewer');
        });
    }
};
