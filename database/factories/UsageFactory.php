<?php

namespace Database\Factories;

use App\Models\Place;
use App\Models\Thing;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usage>
 */
class UsageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thing_id' => Thing::factory(),
            'place_id' => Place::factory(),
            'user_id' => User::factory(),
            'amount' => fake()->numberBetween(1, 10),
            'unit_id' => fake()->optional(0.7)->randomElement([1, 2, 3, 4, 5]), // 70% вероятность наличия размерности
        ];
    }
}
