<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('units')->insert([
            ['name' => 'ແກ້ວ'],
            ['name' => 'ກະປ໋ອງ'],
            ['name' => 'ຖົງ'],
            ['name' => 'ກ່ອງ'],
            ['name' => 'ອັນ'],
            ['name' => 'ຫໍ່'],
            ['name' => 'ແພັກ'],
        ]);
    }
}
