<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'ເຄື່ອງດື່ມ'],
            ['name' => 'ອາຫານວ່າງ'],
            ['name' => 'ເຄື່ອງໃຊ້ໃນບ້ານ'],
            ['name' => 'ອາຫານກະປ໋ອງ'],
            ['name' => 'ເຄື່ອງສຳອາງ'],
            ['name' => 'ເຄື່ອງປຸງແຕ່ງ'],
        ]);
    }
}
