<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('vehicle_count')->default(0);
            $table->integer('job_count')->default(0);
            $table->integer('uninvoiced_count')->default(0);
            $table->integer('invoiced_count')->default(0);
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('estimated_sales', 15, 2)->default(0);
            $table->timestamps();
            
            $table->index('name');
            $table->index('job_count');
            $table->index('total_sales');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_summaries');
    }
};
