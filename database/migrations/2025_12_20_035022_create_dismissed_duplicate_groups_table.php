<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dismissed_duplicate_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_hash', 64)->unique(); // SHA256 hash of sorted names in group
            $table->json('names'); // Array of names in this group
            $table->string('dismissed_by')->nullable();
            $table->string('reason')->nullable(); // 'not_duplicate', 'merged', etc.
            $table->timestamps();
            
            $table->index('group_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dismissed_duplicate_groups');
    }
};
