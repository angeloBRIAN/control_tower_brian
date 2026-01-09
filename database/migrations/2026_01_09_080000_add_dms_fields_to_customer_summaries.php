<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add DMS customer fields to customer_summaries for unified view
     */
    public function up(): void
    {
        Schema::table('customer_summaries', function (Blueprint $table) {
            // Link to customers table (DMS imported)
            $table->foreignId('customer_id')->nullable()->after('name')->constrained()->nullOnDelete();
            
            // DMS fields for display
            $table->string('dms_magic')->nullable()->after('customer_id');
            $table->string('email')->nullable()->after('dms_magic');
            $table->string('phone')->nullable()->after('email');
            $table->string('company_name')->nullable()->after('phone');
            
            // Index for customer lookup
            $table->index('customer_id');
            $table->index('dms_magic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_summaries', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'dms_magic', 'email', 'phone', 'company_name']);
        });
    }
};
