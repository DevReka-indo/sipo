<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seri_berkas', function (Blueprint $table) {
            $table->increments('id_seri');
            $table->integer('seri_bulanan');
            $table->integer('seri_tahunan');
            $table->integer('bulan');
            $table->integer('tahun');
            $table->timestamps();
            $table->string('kode', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seri_berkas');
    }
};
