<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'jakarta pusat'],
            ['name' => 'jakarta selatan'],
            ['name' => 'jakarta utara'],
            ['name' => 'jakarta barat'],
            ['name' => 'jakarta timur'],
            ['name' => 'bandung'],
            ['name' => 'bogor'],
            ['name' => 'depok'],
            ['name' => 'bekasi'],
            ['name' => 'malang'],
            ['name' => 'semarang'],
            ['name' => 'tangerang'],
            ['name' => 'surabaya'],
            ['name' => 'yogyakarta'],
            ['name' => 'lampung'],
            ['name' => 'palembang'],
            ['name' => 'medan'],
            ['name' => 'padang'],
        ];

        City::insert($data);
    }
}
