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
        Schema::create('part_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            
            // Part details
            $table->string('part_name');
            $table->string('part_number')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('rq')->nullable(); // Requisition document number
            $table->string('no_order_part')->nullable(); // Part order number (any supplier)
            $table->text('notes')->nullable(); // Notes/remarks
            
            // Dates
            $table->date('order_date');
            $table->date('expected_date');
            $table->date('received_date')->nullable();
            
            // Status: pending (RQ stage), ordered, confirmed, shipped, received, installed, cancelled
            $table->string('status')->default('pending');
            
            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Indexes
            $table->index(['job_id', 'status']);
            $table->index('expected_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_orders');
    }
};
