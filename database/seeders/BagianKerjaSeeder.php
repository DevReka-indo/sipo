<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BagianKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            // Divisi
            [
                'kode_bagian' => 'SAR',
                'nama_bagian' => 'Divisi Pemasaran',
                'kategori' => 'Divisi',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_bagian' => 'LOG',
                'nama_bagian' => 'Divisi Logistik',
                'kategori' => 'Divisi',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_bagian' => 'KEU',
                'nama_bagian' => 'Divisi Keuangan & Akuntansi',
                'kategori' => 'Divisi',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_bagian' => 'SDU',
                'nama_bagian' => 'Divisi SDM & Umum',
                'kategori' => 'Divisi',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Departemen
            [
                'kode_bagian' => 'SEC',
                'nama_bagian' => 'Departemen Sekretaris Perusahaan',
                'kategori' => 'Departemen',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_bagian' => 'MMLH',
                'nama_bagian' => 'Departemen MM&LH',
                'kategori' => 'Departemen',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_bagian' => 'KEU',
                'nama_bagian' => 'Divisi Keuangan & Akuntansi',
                'kategori' => 'Divisi',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_bagian' => 'SDU',
                'nama_bagian' => 'Divisi SDM & Umum',
                'kategori' => 'Divisi',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_bagian' => 'MRH',
                'nama_bagian' => 'Departemen Manajemen Risiko dan Hukum',
                'kategori' => 'Departemen',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_bagian' => 'ENG',
                'nama_bagian' => 'Departemen Engineering',
                'kategori' => 'Departemen',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_bagian' => 'DSN',
                'nama_bagian' => 'Departemen Desain',
                'kategori' => 'Departemen',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_bagian' => 'QCAS',
                'nama_bagian' => 'Departemen QC&AS',
                'kategori' => 'Departemen',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_bagian' => 'TI',
                'nama_bagian' => 'Departemen Teknologi Informasi',
                'kategori' => 'Departemen',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_bagian' => 'PPO',
                'nama_bagian' => 'Departemen Perencanaan dan Pengendalian Operasi',
                'kategori' => 'Departemen',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_bagian' => 'PROD-MEK',
                'nama_bagian' => 'Departemen Produksi Mekanik',
                'kategori' => 'Departemen',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_bagian' => 'PROD-EL',
                'nama_bagian' => 'Departemen Produksi Elektrik',
                'kategori' => 'Departemen',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Tim
            [
                'kode_bagian' => 'KPO',
                'nama_bagian' => 'Tim Keprojekan Divisi Operasi',
                'kategori' => 'Tim',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Spesialis
            [
                'kode_bagian' => 'S-TQCS',
                'nama_bagian' => 'Spesialis Divisi Teknologi & QCAS',
                'kategori' => 'Spesialis',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Satuan
            [
                'kode_bagian' => 'SPI',
                'nama_bagian' => 'Satuan Pengawas Intern',
                'kategori' => 'Satuan',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert data menggunakan upsert untuk menghindari duplikasi
        foreach ($data as $item) {
            DB::table('bagian_kerja')->updateOrInsert(
                ['kode_bagian' => $item['kode_bagian']],
                $item
            );
        }

        $this->command->info('Bagian Kerja data seeded successfully!');
    }
}
