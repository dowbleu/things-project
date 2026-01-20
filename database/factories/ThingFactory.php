<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Thing>
 */
class ThingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $thingNames = [
            'Дрель электрическая',
            'Перфоратор',
            'Шуруповерт',
            'Болгарка',
            'Циркулярная пила',
            'Лобзик электрический',
            'Шлифовальная машинка',
            'Фен строительный',
            'Паяльник',
            'Мультиметр',
            'Отвертки набор',
            'Ключи гаечные набор',
            'Молоток',
            'Кувалда',
            'Лопата штыковая',
            'Лопата совковая',
            'Грабли',
            'Топор',
            'Ножовка',
            'Рулетка 5 метров',
            'Рулетка 10 метров',
            'Уровень строительный',
            'Краска белая',
            'Краска синяя',
            'Кисть малярная',
            'Валик для покраски',
            'Ведро пластмассовое',
            'Лестница стремянка',
            'Лестница приставная',
            'Трос стальной',
            'Канат',
            'Провод электрический',
            'Розетка электрическая',
            'Выключатель',
            'Лампа настольная',
            'Светильник',
            'Проводка',
            'Кабель витой',
            'Блок питания',
            'Аккумулятор',
        ];
        
        $descriptions = [
            'Исправное состояние',
            'Требует проверки',
            'В хорошем состоянии',
            'Новое',
            'Было в использовании',
            'Требует обслуживания',
            'Готово к использованию',
            'Требует ремонта',
        ];
        
        return [
            'name' => fake()->randomElement($thingNames),
            'description' => fake()->optional(0.6)->randomElement($descriptions),
            'wrnt' => fake()->optional(0.4)->dateTimeBetween('now', '+2 years'),
            'master' => User::factory(),
        ];
    }
}
