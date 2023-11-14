<?php

namespace Database\Seeders;

use App\Models\StatusHistory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'pesan dikirim','created_at' => now(), 'updated_at' => now(),],
            ['name' => 'pesan disetujui','created_at' => now(), 'updated_at' => now(),],
            ['name' => 'perjalanan dimulai','created_at' => now(), 'updated_at' => now(),],
            ['name' => 'sampai ditujuan','created_at' => now(), 'updated_at' => now(),],
            ['name' => 'dalam perjalanan pulang','created_at' => now(), 'updated_at' => now(),],
            ['name' => 'sampai dikepulangan','created_at' => now(), 'updated_at' => now(),],
            ['name' => 'perjalanan selesai','created_at' => now(), 'updated_at' => now(),],
            ['name' => 'pesan dibatalkan','created_at' => now(), 'updated_at' => now(),],
            ['name' => 'pesan ditolak','created_at' => now(), 'updated_at' => now(),],
        ];

        StatusHistory::insert($data);
    }
}
