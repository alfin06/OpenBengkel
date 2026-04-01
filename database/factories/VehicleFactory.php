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
        return [
            'plate_number' => fake()->regexify('[H] [1-9]{4} [A-Z]{2}'), // Semarang (H) plates
            'brand' => fake()->randomElement(['Toyota', 'Honda', 'Suzuki', 'Daihatsu']),
            'model' => fake()->randomElement(['Avanza', 'Brio', 'Ertiga', 'Xenia', 'Innova']),
            'year' => fake()->year(),
        ];
    }
}
