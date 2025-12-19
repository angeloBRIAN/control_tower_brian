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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type'); // Model class name (e.g., App\Models\Booking)
            $table->unsignedBigInteger('auditable_id'); // Model primary key
            $table->unsignedBigInteger('user_id')->nullable(); // Who made the change
            $table->string('action'); // created, updated, deleted
            $table->json('old_values')->nullable(); // Previous values (for update/delete)
            $table->json('new_values')->nullable(); // New values (for create/update)
            $table->string('ip_address')->nullable(); // IP address of the user
            $table->string('user_agent')->nullable(); // Browser/client info
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
