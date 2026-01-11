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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content'); // HTML from WYSIWYG
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_important')->default(false); // Highlighted styling
            $table->boolean('is_pinned')->default(false); // Always show at top
            $table->boolean('send_push')->default(true); // Send push notification
            $table->json('target_roles')->nullable(); // null = all, array = specific roles
            $table->timestamp('published_at')->nullable(); // Schedule or null for immediate
            $table->timestamp('expires_at')->nullable(); // Auto-hide after this date
            $table->json('dismissed_by')->nullable(); // User IDs who dismissed
            $table->timestamps();
            
            $table->index(['published_at', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
