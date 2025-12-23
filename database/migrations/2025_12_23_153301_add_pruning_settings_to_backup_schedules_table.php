<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backup_schedules', function (Blueprint $table) {
            // Pruning settings like borgbackup
            $table->boolean('prune_enabled')->default(true);
            $table->integer('keep_daily')->default(7);    // Keep last 7 daily backups
            $table->integer('keep_weekly')->default(4);   // Keep last 4 weekly backups
            $table->integer('keep_monthly')->default(6);  // Keep last 6 monthly backups
        });
    }

    public function down(): void
    {
        Schema::table('backup_schedules', function (Blueprint $table) {
            $table->dropColumn(['prune_enabled', 'keep_daily', 'keep_weekly', 'keep_monthly']);
        });
    }
};
