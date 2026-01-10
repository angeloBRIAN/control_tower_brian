<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add source column to vehicles to track data origin
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('source', 20)->default('job_import')->after('import_id');
            // Values: 'dms', 'job_import', 'manual'
        });

        // Mark existing vehicles with customer_id as likely from DMS
        // (since DMS import links customers)
        \DB::table('vehicles')
            ->whereNotNull('customer_id')
            ->update(['source' => 'dms']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
