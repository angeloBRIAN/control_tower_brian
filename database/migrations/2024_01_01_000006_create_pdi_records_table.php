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
        Schema::create('pdi_records', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number');
            $table->string('vin')->nullable();
            $table->string('model')->nullable();
            $table->date('pdi_date');
            $table->string('technician')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, completed
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('pdi_date');
            $table->index('plate_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdi_records');
    }
};
