<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kirim_document', function (Blueprint $table) {
            $table->increments('id_kirim_document');
            $table->integer('id_document')->nullable();
            $table->string('jenis_document', 45)->nullable();
            $table->bigInteger('id_pengirim')->nullable();
            $table->bigInteger('id_penerima')->nullable();
            $table->string('status', 45)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kirim_document');
    }
};
