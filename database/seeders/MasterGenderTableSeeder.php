<?php

namespace Database\Seeders;

use App\Models\MasterGender;
use Illuminate\Database\Seeder;


class MasterGenderTableSeeder extends Seeder
{
    public function run()
    {
        $seedGender = array(
            ['gender' => 'Laki-Laki'],
            ['gender' => 'Perempuan']
        );
        MasterGender::insert($seedGender);
    }
}
