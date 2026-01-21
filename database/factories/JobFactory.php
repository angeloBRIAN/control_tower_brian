<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_number' => 'WIP-' . $this->faker->unique()->numberBetween(10000, 99999),
            'plate_number' => strtoupper($this->faker->randomLetter()) . ' ' . $this->faker->numberBetween(1000, 9999) . ' ' . strtoupper($this->faker->randomLetter() . $this->faker->randomLetter()),
            'customer_name' => $this->faker->company(),
            'customer_address' => $this->faker->address(),
            'service_advisor' => $this->faker->name(),
            'technician' => $this->faker->name(),
            'foreman' => $this->faker->name(),
            'job_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'job_type' => $this->faker->randomElement(['Service', 'Repair', 'PDI', 'Warranty']),
            'department' => $this->faker->randomElement(['W', 'B']),
            'status' => 'uninvoiced',
            'work_status' => $this->faker->randomElement(\App\Models\Job::WORK_STATUSES),
            'estimated_amount' => $this->faker->randomFloat(2, 100000, 10000000),
            'total_sales' => $this->faker->randomFloat(2, 100000, 10000000),
            'need_part' => $this->faker->boolean(30),
        ];
    }

    /**
     * Indicate that the job is invoiced.
     */
    public function invoiced(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'invoiced',
            'invoiced_at' => now(),
            'invoice_number' => 'INV-' . $this->faker->unique()->numberBetween(10000, 99999),
        ]);
    }

    /**
     * Indicate that the job needs parts.
     */
    public function needsParts(): static
    {
        return $this->state(fn (array $attributes) => [
            'need_part' => true,
        ]);
    }
}
