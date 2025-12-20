<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dropdown_options', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // work_status, payment_type, technician, block
            $table->string('value'); // The actual value stored in jobs table
            $table->string('label'); // Display label
            $table->string('icon')->nullable(); // Bootstrap icon name (e.g., 'hourglass-split')
            $table->string('color')->default('secondary'); // Bootstrap color (primary, success, warning, etc.)
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['type', 'value']);
            $table->index('type');
            $table->index(['type', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dropdown_options');
    }
};
