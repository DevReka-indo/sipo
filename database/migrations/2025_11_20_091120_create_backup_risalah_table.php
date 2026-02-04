<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_risalah', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_document');
            $table->string('jenis_document', 255);
            $table->timestamp('tgl_dibuat')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('tgl_disahkan')->nullable();
            $table->integer('seri_document');
            $table->string('nomor_document', 255);
            $table->string('tujuan', 255);
            $table->string('waktu_mulai', 255);
            $table->string('waktu_selesai', 255);
            $table->string('agenda', 255);
            $table->string('tempat', 255);
            $table->string('nama_bertandatangan', 255);
            $table->longText('lampiran')->nullable();
            $table->string('judul', 255);
            $table->longText('catatan')->nullable();
            $table->integer('divisi_id_divisi');
            $table->enum('status', ['pending', 'approve', 'reject'])->default('pending');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('pembuat', 45);

            $table->primary(['id', 'divisi_id_divisi']);
            $table->index('divisi_id_divisi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_risalah');
    }
};
