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
        // Add 'billing' to the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'control_tower', 'finance', 'billing', 'sparepart', 'sa', 'foreman', 'audit', 'user') DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'billing' from enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'control_tower', 'finance', 'sparepart', 'sa', 'foreman', 'audit', 'user') DEFAULT 'user'");
    }
};
