<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->increments('id_laporan');
            $table->string('judul', 255)->nullable();
            $table->timestamp('tgl_dibuat')->nullable();
            $table->timestamp('tgl_disahkan')->nullable();
            $table->enum('status', ['pending', 'approve', 'reject'])->nullable();
            $table->string('nomor_laporan', 255)->nullable();
            $table->timestamps();
            $table->unsignedInteger('divisi_id_divisi')->nullable();
            $table->integer('seri_surat')->nullable();

            $table->foreign('divisi_id_divisi')
                ->references('id_divisi')
                ->on('divisi')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
