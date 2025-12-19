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
        Schema::create('customer_merge_logs', function (Blueprint $table) {
            $table->id();
            $table->string('old_name');
            $table->string('canonical_name');
            $table->string('source_type')->nullable(); // 'dms_import', 'job_progress_import', 'user_entry'
            $table->integer('jobs_updated')->default(0);
            $table->integer('vehicles_updated')->default(0);
            $table->string('merged_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('old_name');
            $table->index('canonical_name');
            $table->index('source_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_merge_logs');
    }
};
