<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arsip', function (Blueprint $table) {
            $table->increments('id_arsip');
            $table->integer('document_id');
            $table->unsignedBigInteger('user_id');
            $table->string('jenis_document', 45);

            $table->primary(['id_arsip', 'document_id', 'user_id']);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip');
    }
};
