<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memo', function (Blueprint $table) {
            $table->increments('id_memo');
            $table->string('judul', 255);
            $table->string('tujuan', 255);
            $table->text('isi_memo');
            $table->date('tgl_dibuat');
            $table->date('tgl_disahkan')->nullable();
            $table->longText('qr_approved_by')->nullable();
            $table->enum('status', ['pending', 'approve', 'reject', 'correction'])->default('pending');
            $table->string('nomor_memo', 255);
            $table->string('nama_bertandatangan', 255);
            $table->string('kode', 10)->nullable();
            $table->timestamps();
            $table->string('seri_surat', 10);
            $table->string('pembuat', 255);
            $table->longText('catatan')->nullable();
            $table->text('tujuan_string')->nullable();
            $table->softDeletes();

            $table->index('pembuat');
        });

        DB::statement("ALTER TABLE memo ADD lampiran LONGBLOB;");
    }

    public function down(): void
    {
        Schema::dropIfExists('memo');
    }
};
