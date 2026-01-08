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
            $table->string('rq')->nullable(); // Requisition number
            $table->string('no_order_part_mbina')->nullable(); // MBINA order number
            $table->text('notes')->nullable(); // Notes/remarks (replaces lain_lain)
            
            // Dates
            $table->date('order_date');
            $table->date('expected_date');
            $table->date('received_date')->nullable();
            
            // Status: ordered, confirmed, shipped, received, installed, cancelled
            $table->string('status')->default('ordered');
            
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
