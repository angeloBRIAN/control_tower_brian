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
        // Add 'finance' to the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'control_tower', 'finance', 'sparepart', 'sa', 'foreman', 'audit', 'user') DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'finance' from enum (users with finance role will need to be updated first)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'control_tower', 'sparepart', 'sa', 'foreman', 'audit', 'user') DEFAULT 'user'");
    }
};
