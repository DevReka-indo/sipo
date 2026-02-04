<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risalah_details', function (Blueprint $table) {
            $table->bigIncrements('id_risalah_detail');
            $table->unsignedBigInteger('risalah_id_risalah');
            $table->integer('nomor')->nullable();
            $table->longText('topik')->nullable();
            $table->longText('pembahasan')->nullable();
            $table->longText('tindak_lanjut')->nullable();
            $table->longText('target')->nullable();
            $table->longText('pic')->nullable();
            $table->timestamps();

            $table->index('risalah_id_risalah');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risalah_details');
    }
};
