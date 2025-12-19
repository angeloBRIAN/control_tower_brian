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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('import_id')->nullable()->after('is_in_workshop')->constrained('imports')->nullOnDelete();
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->foreignId('import_id')->nullable()->after('invoiced_at')->constrained('imports')->nullOnDelete();
        });

        Schema::table('pdi_records', function (Blueprint $table) {
            $table->foreignId('import_id')->nullable()->after('notes')->constrained('imports')->nullOnDelete();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('import_id')->nullable()->after('notes')->constrained('imports')->nullOnDelete();
        });

        Schema::table('towing_records', function (Blueprint $table) {
            $table->foreignId('import_id')->nullable()->after('notes')->constrained('imports')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('import_id');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('import_id');
        });

        Schema::table('pdi_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('import_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('import_id');
        });

        Schema::table('towing_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('import_id');
        });
    }
};
