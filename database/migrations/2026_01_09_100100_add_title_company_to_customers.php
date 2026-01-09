<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add title and company_id to customers table
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Title field (Mr/Mrs/PT/CV etc)
            $table->string('title', 20)->nullable()->after('name');
            
            // Link to companies table
            $table->foreignId('company_id')->nullable()->after('title')->constrained()->nullOnDelete();
            
            // Store non-phone data/notes from telp fields
            $table->text('phone_notes')->nullable()->after('phone_4');
            
            // Index for company lookup
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['title', 'company_id', 'phone_notes']);
        });
    }
};
