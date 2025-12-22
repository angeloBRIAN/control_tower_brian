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
        Schema::create('report_settings', function (Blueprint $table) {
            $table->id();
            $table->string('report_type')->unique(); // e.g., 'weekly', 'monthly'
            $table->string('name'); // Display name
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->string('schedule')->default('weekly'); // weekly, daily, monthly
            $table->string('schedule_time')->default('08:00');
            $table->string('schedule_day')->nullable(); // For weekly: 1=Monday, For monthly: 1-31
            $table->json('recipients')->nullable(); // Array of email addresses
            $table->timestamps();
        });

        // SMTP Settings table
        Schema::create('smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('default');
            $table->string('host')->nullable();
            $table->integer('port')->default(587);
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // Encrypted
            $table->string('encryption')->default('tls'); // tls, ssl, null
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default report settings
        \DB::table('report_settings')->insert([
            [
                'report_type' => 'weekly',
                'name' => 'Weekly Workshop Report',
                'description' => 'Summary of jobs, revenue, and performance for the past week',
                'is_enabled' => true,
                'schedule' => 'weekly',
                'schedule_time' => '08:00',
                'schedule_day' => '1', // Monday
                'recipients' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
        Schema::dropIfExists('report_settings');
    }
};
