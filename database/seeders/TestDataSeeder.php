<?php

namespace Database\Seeders;

use App\Models\Place;
use App\Models\Thing;
use App\Models\Usage;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем русских пользователей с понятными именами
        $users = collect([
            ['name' => 'Иван Петров', 'email' => 'ivan.petrov@example.ru'],
            ['name' => 'Мария Сидорова', 'email' => 'maria.sidorova@example.ru'],
            ['name' => 'Александр Смирнов', 'email' => 'alexandr.smirnov@example.ru'],
            ['name' => 'Елена Козлова', 'email' => 'elena.kozlova@example.ru'],
            ['name' => 'Дмитрий Волков', 'email' => 'dmitry.volkov@example.ru'],
        ])->map(function ($userData) {
            return User::factory()->create([
                'name' => $userData['name'],
                'email' => $userData['email'],
            ]);
        });

        // Создаем правдоподобные места хранения
        $repairPlace = Place::create([
            'name' => 'Ремонтная мастерская',
            'description' => 'Место для ремонта и обслуживания инструментов',
            'repair' => true,
            'work' => false,
        ]);
        
        $workPlace = Place::create([
            'name' => 'Рабочее место на стройке',
            'description' => 'Активное рабочее место',
            'repair' => false,
            'work' => true,
        ]);
        
        $normalPlaces = collect([
            ['name' => 'Склад на первом этаже', 'description' => 'Основное место хранения инструментов'],
            ['name' => 'Гараж', 'description' => 'Хранение крупногабаритных инструментов'],
            ['name' => 'Подвал', 'description' => 'Долгосрочное хранение'],
            ['name' => 'Кладовка в офисе', 'description' => 'Хранение офисного инвентаря'],
            ['name' => 'Склад инструментов', 'description' => 'Специализированный склад для инструментов'],
        ])->map(function ($placeData) {
            return Place::create($placeData);
        });

        // Создаем понятные вещи для каждого пользователя
        $thingsData = [
            'Иван Петров' => [
                ['name' => 'Дрель электрическая Bosch', 'description' => 'Мощная дрель для работы с бетоном', 'wrnt' => now()->addYear()],
                ['name' => 'Перфоратор Makita', 'description' => 'Профессиональный перфоратор', 'wrnt' => now()->addMonths(18)],
                ['name' => 'Шуруповерт аккумуляторный', 'description' => 'Удобный для работы без проводов', 'wrnt' => now()->addYear()],
            ],
            'Мария Сидорова' => [
                ['name' => 'Краска белая 10 литров', 'description' => 'Водоэмульсионная краска', 'wrnt' => now()->addMonths(6)],
                ['name' => 'Кисти малярные набор', 'description' => 'Набор из 5 кистей разного размера'],
                ['name' => 'Валик для покраски', 'description' => 'Валик с длинной ручкой'],
            ],
            'Александр Смирнов' => [
                ['name' => 'Болгарка DeWalt', 'description' => 'Углошлифовальная машина', 'wrnt' => now()->addYear()],
                ['name' => 'Циркулярная пила', 'description' => 'Для распила дерева и ДСП'],
                ['name' => 'Лобзик электрический', 'description' => 'Для фигурного реза'],
            ],
            'Елена Козлова' => [
                ['name' => 'Лопата штыковая', 'description' => 'Для земляных работ'],
                ['name' => 'Грабли садовые', 'description' => 'Для работы в саду'],
                ['name' => 'Топор', 'description' => 'Для рубки дров'],
            ],
            'Дмитрий Волков' => [
                ['name' => 'Мультиметр цифровой', 'description' => 'Для измерения электрических параметров', 'wrnt' => now()->addMonths(24)],
                ['name' => 'Паяльник 60Вт', 'description' => 'Для пайки проводов'],
                ['name' => 'Отвертки набор', 'description' => 'Набор из 10 отверток'],
            ],
        ];

        foreach ($users as $user) {
            $userThings = $thingsData[$user->name] ?? [];
            
            foreach ($userThings as $thingData) {
                $thing = Thing::create([
                    'name' => $thingData['name'],
                    'description' => $thingData['description'] ?? null,
                    'wrnt' => $thingData['wrnt'] ?? null,
                    'master' => $user->id,
                ]);

                // Случайно выбираем место
                $place = collect([$repairPlace, $workPlace, ...$normalPlaces])->random();
                
                // Случайно выбираем пользователя (может быть владелец или другой)
                $usageUser = $users->random();

                Usage::factory()->create([
                    'thing_id' => $thing->id,
                    'place_id' => $place->id,
                    'user_id' => $usageUser->id,
                    'amount' => fake()->numberBetween(1, 5),
                    'unit_id' => fake()->optional(0.7)->randomElement([1, 2, 3, 4, 5]), // штуки, кг, г, л, мл
                ]);
            }
        }
    }
}
