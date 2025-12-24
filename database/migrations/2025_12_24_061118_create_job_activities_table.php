<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable(); // Store name in case user deleted
            $table->string('action'); // created, updated, status_changed, remark_added, etc.
            $table->string('description'); // Human-readable description
            $table->json('changes')->nullable(); // Old/new values for updates
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->index(['job_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_activities');
    }
};
