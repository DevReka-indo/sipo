<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risalah', function (Blueprint $table) {
            $table->increments('id_risalah');
            $table->string('judul', 255);
            $table->timestamp('tgl_dibuat')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('tgl_disahkan')->nullable();
            $table->longText('qr_approved_by')->nullable();
            $table->enum('status', ['pending', 'approve', 'reject', 'correction'])->default('pending');
            $table->string('nomor_risalah', 255);
            $table->string('nama_bertandatangan', 255);
            $table->string('kode', 10)->nullable();
            $table->string('seri_surat', 10);
            $table->timestamps();
            $table->unsignedBigInteger('pembuat')->nullable();
            $table->string('waktu_mulai', 50);
            $table->string('waktu_selesai', 50);
            $table->string('agenda', 255);
            $table->string('tempat', 255);
            $table->longText('catatan')->nullable();
            $table->softDeletes();

            $table->foreign('pembuat')
                ->references('id')
                ->on('users');

            $table->index('pembuat');
        });
        DB::statement("ALTER TABLE risalah ADD lampiran LONGBLOB;");
    }

    public function down(): void
    {
        Schema::dropIfExists('risalah');
    }
};
