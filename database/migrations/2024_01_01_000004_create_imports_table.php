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
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_path')->nullable();
            $table->string('import_type'); // uninvoiced, invoiced, progress
            $table->integer('records_imported')->default(0);
            $table->integer('records_updated')->default(0);
            $table->integer('records_failed')->default(0);
            $table->text('notes')->nullable();
            $table->string('imported_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};
