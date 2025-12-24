<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backup_schedules', function (Blueprint $table) {
            $table->boolean('session_cleanup_enabled')->default(true);
            $table->integer('session_cleanup_days')->default(7); // Clean up sessions inactive for 7 days
        });
    }

    public function down(): void
    {
        Schema::table('backup_schedules', function (Blueprint $table) {
            $table->dropColumn(['session_cleanup_enabled', 'session_cleanup_days']);
        });
    }
};
