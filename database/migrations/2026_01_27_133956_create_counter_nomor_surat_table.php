<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('counter_nomor_surat', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_permintaan');
            $table->string('seri_pada_tahun_berjalan', 10); // 01, 02, 03, dst
            $table->string('seri_pada_bulan_berjalan', 10); // 01, 02, 03, dst
            $table->string('perusahaan', 100);
            $table->string('kode_tipe_surat', 20); // GEN, SPK, dll
            $table->string('divisi', 20); // Menggunakan kode_bagian dari tabel bagian_kerja
            $table->string('bulan', 20); // Nama bulan: I, II, III atau January, February
            $table->year('tahun');
            $table->string('pic_peminta', 100);
            $table->string('jenis', 50); // Klasifikasi jenis dokumen: Memo, Surat, Kontrak, dll
            $table->text('perihal');
            $table->string('nomor_surat_generated', 255)->nullable(); // Hasil generate nomor surat
            $table->boolean('is_used')->default(true); // Status apakah nomor sudah digunakan
            $table->timestamps();

            // Indexes
            $table->index('tanggal_permintaan');
            $table->index('kode_tipe_surat');
            $table->index('divisi');
            $table->index('tahun');
            $table->index('bulan');
            $table->index('jenis');
            $table->index('nomor_surat_generated');

            // Composite index untuk pencarian yang sering dilakukan
            $table->index(['tahun', 'bulan', 'kode_tipe_surat']);

            // Foreign key ke tabel bagian_kerja
            $table->foreign('divisi')
                  ->references('kode_bagian')
                  ->on('bagian_kerja')
                  ->onUpdate('cascade')
                  ->onDelete('restrict'); // Tidak bisa hapus bagian_kerja jika masih ada counter yang menggunakannya
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counter_nomor_surat');
    }
};
