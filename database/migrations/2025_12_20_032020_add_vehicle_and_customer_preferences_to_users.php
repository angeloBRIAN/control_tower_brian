<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('vehicle_preferences')->nullable()->after('towing_preferences');
            $table->json('customer_preferences')->nullable()->after('vehicle_preferences');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['vehicle_preferences', 'customer_preferences']);
        });
    }
};
