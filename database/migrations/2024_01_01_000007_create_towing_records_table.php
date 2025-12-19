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
        Schema::create('towing_records', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('pickup_location')->nullable();
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->string('job_type')->default('towing'); // towing, storing
            $table->string('status')->default('scheduled'); // scheduled, in_progress, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('scheduled_date');
            $table->index('plate_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('towing_records');
    }
};
