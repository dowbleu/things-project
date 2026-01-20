<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Place>
 */
class PlaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $placeNames = [
            'Склад на первом этаже',
            'Гараж',
            'Подвал',
            'Чердак',
            'Кладовка в офисе',
            'Склад на втором этаже',
            'Склад инструментов',
            'Склад материалов',
            'Склад готовой продукции',
            'Архив',
            'Серверная',
            'Офисное помещение',
            'Производственный цех',
            'Склад запчастей',
            'Склад упаковки',
        ];
        
        $descriptions = [
            'Основное место хранения',
            'Склад для длительного хранения',
            'Временное место хранения',
            'Место для активного использования',
            'Склад для сезонных вещей',
            'Место для хранения инструментов',
            'Склад для материалов',
            'Место для готовой продукции',
        ];
        
        return [
            'name' => fake()->randomElement($placeNames),
            'description' => fake()->optional(0.7)->randomElement($descriptions),
            'repair' => fake()->boolean(20), // 20% вероятность repair = true
            'work' => fake()->boolean(30), // 30% вероятность work = true
        ];
    }
}
