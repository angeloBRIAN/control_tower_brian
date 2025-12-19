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
        // Bookings: make plate_number nullable and add wip column
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('plate_number')->nullable()->change();
            $table->string('wip')->nullable()->after('plate_number');
            $table->string('foreman')->nullable()->after('service_type');
            $table->string('service_advisor')->nullable()->after('foreman');
            $table->index('wip');
        });

        // PDI Records: make plate_number nullable and add wip column
        Schema::table('pdi_records', function (Blueprint $table) {
            $table->string('plate_number')->nullable()->change();
            $table->string('wip')->nullable()->after('vin');
            $table->string('colour')->nullable()->after('model');
            $table->string('engine_no')->nullable()->after('vin');
            $table->index('vin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['wip']);
            $table->dropColumn(['wip', 'foreman', 'service_advisor']);
            $table->string('plate_number')->nullable(false)->change();
        });

        Schema::table('pdi_records', function (Blueprint $table) {
            $table->dropIndex(['vin']);
            $table->dropColumn(['wip', 'colour', 'engine_no']);
            $table->string('plate_number')->nullable(false)->change();
        });
    }
};
