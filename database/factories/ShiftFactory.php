<?php

namespace Database\Factories;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shift>
 */
class ShiftFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Shift::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $shifts = [
            [
                'name' => 'Morning Shift',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'description' => 'Standard morning shift for production'
            ],
            [
                'name' => 'Evening Shift',
                'start_time' => '16:00:00',
                'end_time' => '00:00:00',
                'description' => 'Evening shift for continuous production'
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '00:00:00',
                'end_time' => '08:00:00',
                'description' => 'Night shift for 24/7 operations'
            ],
            [
                'name' => 'Day Shift',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'description' => 'Standard day shift for administration'
            ]
        ];

        $selectedShift = $this->faker->randomElement($shifts);

        return [
            'name' => $selectedShift['name'],
            'description' => $selectedShift['description'],
            'start_time' => $selectedShift['start_time'],
            'end_time' => $selectedShift['end_time'],
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }

    /**
     * Indicate that the shift is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the shift is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a morning shift (8 AM - 4 PM).
     */
    public function morning(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Morning Shift',
            'description' => 'Standard morning shift',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);
    }

    /**
     * Create an evening shift (4 PM - 12 AM).
     */
    public function evening(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Evening Shift',
            'description' => 'Standard evening shift',
            'start_time' => '16:00:00',
            'end_time' => '00:00:00',
        ]);
    }

    /**
     * Create a night shift (12 AM - 8 AM).
     */
    public function night(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Night Shift',
            'description' => 'Standard night shift',
            'start_time' => '00:00:00',
            'end_time' => '08:00:00',
        ]);
    }
}
