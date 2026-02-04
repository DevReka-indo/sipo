<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('undangan', function (Blueprint $table) {
            $table->increments('id_undangan');
            $table->string('judul', 255);
            $table->string('tujuan', 255);
            $table->longText('isi_undangan');
            $table->dateTime('tgl_dibuat');
            $table->dateTime('tgl_disahkan')->nullable();
            $table->longText('qr_approved_by')->nullable();
            $table->enum('status', ['pending', 'approve', 'reject', 'correction'])->default('pending');
            $table->string('nomor_undangan', 255);
            $table->string('nama_bertandatangan', 255);
            $table->string('kode', 10)->nullable();
            $table->string('seri_surat', 10);
            $table->timestamps();
            $table->integer('pembuat');
            $table->longText('catatan')->nullable();
            $table->dateTime('tgl_rapat')->nullable();
            $table->string('waktu_mulai', 50);
            $table->string('waktu_selesai', 50);
            $table->string('tempat', 255);
            $table->softDeletes();

            $table->index('pembuat');
        });

        DB::statement("ALTER TABLE undangan ADD lampiran LONGBLOB;");
    }

    public function down(): void
    {
        Schema::dropIfExists('undangan');
    }
};
