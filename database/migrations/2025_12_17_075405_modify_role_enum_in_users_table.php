<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL requires special handling for enum modification
        // First drop the column and recreate with new values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'control_tower', 'sparepart', 'sa', 'foreman', 'audit', 'user') DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'mechanic', 'sa', 'foreman') DEFAULT 'user'");
    }
};
