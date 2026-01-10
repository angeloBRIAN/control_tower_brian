<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add images column to remarks table for comment attachments
     */
    public function up(): void
    {
        Schema::table('remarks', function (Blueprint $table) {
            $table->json('images')->nullable()->after('remark_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remarks', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
