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
        Schema::table('part_orders', function (Blueprint $table) {
            $table->date('order_date')->nullable()->change();
            $table->date('expected_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('part_orders', function (Blueprint $table) {
            // We cannot easily revert to NOT NULL without knowing if there are nulls, 
            // but for down() we can try to make them nullable false if we want strict reversal.
            // However, usually safest to just leave them nullable or set a default.
            // For now, we will try to reverse it, assuming data integrity is managed.
            $table->date('order_date')->nullable(false)->change();
            $table->date('expected_date')->nullable(false)->change();
        });
    }
};
