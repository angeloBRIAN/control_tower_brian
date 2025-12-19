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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->date('booking_date');
            $table->time('booking_time')->nullable();
            $table->string('service_type')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, confirmed, completed, cancelled
            $table->timestamps();
            
            $table->index('booking_date');
            $table->index('plate_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
