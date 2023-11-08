<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'laki-laki','created_at' => now(), 'updated_at' => now(),],
            ['name' => 'perempuan','created_at' => now(), 'updated_at' => now(),],
        ];

        Gender::insert($data);
    }
}
