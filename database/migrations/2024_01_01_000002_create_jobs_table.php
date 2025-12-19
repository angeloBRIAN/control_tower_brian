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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('plate_number')->nullable();
            $table->string('service_advisor')->nullable();
            $table->string('technician')->nullable();
            $table->string('job_type')->nullable(); // regular, PDI, booking, towing
            $table->date('job_date')->nullable();
            $table->date('promise_date')->nullable();
            $table->decimal('estimated_amount', 15, 2)->nullable();
            $table->string('status')->default('uninvoiced'); // uninvoiced, invoiced
            $table->string('work_status')->nullable(); // waiting parts, in progress, etc.
            $table->text('description')->nullable();
            $table->text('latest_remark')->nullable();
            $table->timestamp('latest_remark_at')->nullable();
            $table->string('invoice_number')->nullable();
            $table->timestamp('invoiced_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('job_date');
            $table->index('plate_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
