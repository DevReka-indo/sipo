<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TruncateDocumentTables extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('kirim_document')->truncate();
        DB::table('notifikasi')->truncate();
        DB::table('memo')->truncate();
        DB::table('kategori_barang')->truncate();
        DB::table('risalah')->truncate();
        DB::table('risalah_details')->truncate();
        DB::table('undangan')->truncate();
        DB::table('arsip')->truncate();
    }
}
