<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'position' => $this->faker->randomElement(['Worker', 'Supervisor', 'Manager', 'Operator']),
            'department' => $this->faker->randomElement(['Production', 'Quality Control', 'Maintenance', 'Administration']),
            'hire_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'salary' => $this->faker->numberBetween(3000000, 15000000),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'birth_date' => $this->faker->dateTimeBetween('-50 years', '-18 years'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'marital_status' => $this->faker->randomElement(['single', 'married', 'divorced']),
            'education' => $this->faker->randomElement(['sd', 'smp', 'sma', 'diploma', 'sarjana']),
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'location_id' => 1, // Assume location with ID 1 exists
        ];
    }

    /**
     * Indicate that the employee is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the employee is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
