<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_barang', function (Blueprint $table) {
            $table->increments('id_kategori_barang');
            $table->integer('nomor');
            $table->string('barang', 100);
            $table->integer('qty');
            $table->string('satuan', 50);
            $table->timestamps();
            $table->unsignedInteger('memo_id_memo');
            $table->unsignedInteger('memo_divisi_id_divisi')->nullable();

            $table->index('memo_divisi_id_divisi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_barang');
    }
};
