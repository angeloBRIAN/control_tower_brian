<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add customer linking stats to imports table
     */
    public function up(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->integer('customers_linked')->default(0)->after('records_failed');
            $table->json('customers_unlinked')->nullable()->after('customers_linked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn(['customers_linked', 'customers_unlinked']);
        });
    }
};
