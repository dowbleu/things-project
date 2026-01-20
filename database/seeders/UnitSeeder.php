<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'штуки', 'abbreviation' => 'шт'],
            ['name' => 'килограммы', 'abbreviation' => 'кг'],
            ['name' => 'граммы', 'abbreviation' => 'г'],
            ['name' => 'литры', 'abbreviation' => 'л'],
            ['name' => 'миллилитры', 'abbreviation' => 'мл'],
            ['name' => 'метры', 'abbreviation' => 'м'],
            ['name' => 'сантиметры', 'abbreviation' => 'см'],
            ['name' => 'комплекты', 'abbreviation' => 'компл'],
            ['name' => 'упаковки', 'abbreviation' => 'уп'],
            ['name' => 'пары', 'abbreviation' => 'пар'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['abbreviation' => $unit['abbreviation']],
                $unit
            );
        }
    }
}
