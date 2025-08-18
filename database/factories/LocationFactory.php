<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' ' . $this->faker->randomElement(['Factory', 'Plant', 'Facility', 'Center']),
            'code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{2}'),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'radius' => $this->faker->numberBetween(50, 500),
            'address' => $this->faker->address(),
            'description' => $this->faker->optional()->sentence(),
            'is_active' => $this->faker->boolean(90),
        ];
    }

    /**
     * Indicate that the location is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the location is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
