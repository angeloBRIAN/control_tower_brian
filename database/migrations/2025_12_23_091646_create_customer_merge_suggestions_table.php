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
        Schema::create('customer_merge_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name_a');
            $table->string('customer_name_b');
            $table->float('similarity_score')->default(0);
            $table->integer('jobs_count_a')->default(0);
            $table->integer('jobs_count_b')->default(0);
            $table->enum('status', ['pending', 'merged', 'ignored'])->default('pending');
            $table->timestamps();
            
            $table->index('status');
            $table->index(['customer_name_a', 'customer_name_b']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_merge_suggestions');
    }
};
