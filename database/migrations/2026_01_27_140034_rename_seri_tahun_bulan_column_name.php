<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Migration ini untuk RENAME kolom yang sudah ada
     * dari nama lama ke nama baru yang lebih efektif
     */
    public function up(): void
    {
        Schema::table('counter_nomor_surat', function (Blueprint $table) {
            // Rename kolom
            $table->renameColumn('seri_pada_tahun_berjalan', 'seri_tahun');
            $table->renameColumn('seri_pada_bulan_berjalan', 'seri_bulan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Untuk rollback ke nama lama jika diperlukan
     */
    public function down(): void
    {
        Schema::table('counter_nomor_surat', function (Blueprint $table) {
            // Kembalikan ke nama lama
            $table->renameColumn('seri_tahun', 'seri_pada_tahun_berjalan');
            $table->renameColumn('seri_bulan', 'seri_pada_bulan_berjalan');
        });
    }
};
