<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        $brands = ['Toyota', 'Honda', 'Suzuki', 'Mitsubishi', 'Daihatsu'];
        return [
            'customer_id' => \App\Models\Customer::factory(),
            'plate_number' => fake()->bothify('? #### ??'), // e.g., B 1234 ABC
            'brand' => fake()->randomElement($brands),
            'model' => fake()->word(),
            'year' => fake()->year(),
        ];
    }
}
