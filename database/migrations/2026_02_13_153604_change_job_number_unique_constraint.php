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
        Schema::table('jobs', function (Blueprint $table) {
            // Drop existing unique index on job_number
            $table->dropUnique(['job_number']);
            
            // Add new composite unique index
            $table->unique(['job_number', 'franchise']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Drop composite unique index
            $table->dropUnique(['job_number', 'franchise']);
            
            // Restore unique index on job_number
            $table->unique('job_number');
        });
    }
};
