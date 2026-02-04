<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerusahaanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('perusahaan')->insert([
            'id' => 1,
            'nama_instansi' => 'PT REKAINDO GLOBAL JASA',
            'alamat_web' => 'https://ptrekaindo.co.id',
            'telepon' => '0351-4773030',
            'email' => 'sekretariat@ptrekaindo.co.id',
            'alamat' => 'Jl.Candi Sewu No.30, Madiun 63122',
            'logo' => null, 

            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
