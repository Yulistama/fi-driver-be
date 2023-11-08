<?php

namespace Database\Seeders;

use App\Models\StatusBooking;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusBookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'menunggu','created_at' => now(), 'updated_at' => now(),],
            ['name' => 'disetujui','created_at' => now(), 'updated_at' => now(),],
            ['name' => 'berjalan','created_at' => now(), 'updated_at' => now(),],
            ['name' => 'selesai','created_at' => now(), 'updated_at' => now(),],
            ['name' => 'ditolak','created_at' => now(), 'updated_at' => now(),],
            ['name' => 'dibatalkan','created_at' => now(), 'updated_at' => now(),],
        ];

        StatusBooking::insert($data);
    }
}
