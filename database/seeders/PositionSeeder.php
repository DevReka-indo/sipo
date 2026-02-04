<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('position')->insert([
            ['id_position' => 1, 'nm_position' => 'Direktur', 'kode_position' => null],
            ['id_position' => 2, 'nm_position' => '(GM) General Manager', 'kode_position' => null],
            ['id_position' => 3, 'nm_position' => '(SM) Senior Manager', 'kode_position' => null],
            ['id_position' => 4, 'nm_position' => '(PJ SM) P.J. Senior Manager', 'kode_position' => null],
            ['id_position' => 5, 'nm_position' => '(M) Manager', 'kode_position' => null],
            ['id_position' => 6, 'nm_position' => '(SPV) Supervisor', 'kode_position' => null],
            ['id_position' => 7, 'nm_position' => '(PJ M) P.J. Manager', 'kode_position' => null],
            ['id_position' => 8, 'nm_position' => '(PJ SPV) P.J. Supervisor', 'kode_position' => null],
            ['id_position' => 9, 'nm_position' => 'Staff', 'kode_position' => null],
            ['id_position' => 10, 'nm_position' => '(PLT SPV) Plt. Supervisor', 'kode_position' => null],
            ['id_position' => 11, 'nm_position' => '(PLT M) Plt. Manager', 'kode_position' => null],
            ['id_position' => 12, 'nm_position' => '(PLT SM) Plt. Senior Manager', 'kode_position' => null],
            ['id_position' => 13, 'nm_position' => '(PLT GM) Plt. General Manager', 'kode_position' => null],
            ['id_position' => 14, 'nm_position' => 'Spesialis Madya', 'kode_position' => null],
            ['id_position' => 15, 'nm_position' => 'Spesialis Pratama', 'kode_position' => null],
            ['id_position' => 16, 'nm_position' => 'Spesialis Muda', 'kode_position' => null],
            ['id_position' => 17, 'nm_position' => 'Junior', 'kode_position' => null],
        ]);
    }
}
