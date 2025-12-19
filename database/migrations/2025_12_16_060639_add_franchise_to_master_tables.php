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
        Schema::table('foremen', function (Blueprint $table) {
            $table->string('franchise')->nullable()->after('name'); // PC, CV
        });

        Schema::table('service_advisors', function (Blueprint $table) {
             $table->string('franchise')->nullable()->after('name'); // PC, CV
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foremen', function (Blueprint $table) {
            $table->dropColumn('franchise');
        });

        Schema::table('service_advisors', function (Blueprint $table) {
            $table->dropColumn('franchise');
        });
    }
};
