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
        Schema::create('job_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->enum('invoice_type', ['invoice', 'credit_note'])->default('invoice');
            $table->decimal('inv_amount', 15, 2)->default(0);
            $table->decimal('inv_ppn', 15, 2)->default(0);
            $table->decimal('inv_ppn_meterai', 15, 2)->default(0);
            $table->string('type_sale')->nullable(); // INT, WAR, CASH
            $table->string('notes')->nullable();
            $table->foreignId('import_id')->nullable()->constrained('imports')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['job_id', 'invoice_date']);
            $table->index('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_invoices');
    }
};
