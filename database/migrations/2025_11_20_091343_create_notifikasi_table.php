<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->increments('id_notifikasi');
            $table->string('judul', 100);
            $table->unsignedBigInteger('id_user');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('dibaca');
            $table->string('judul_document', 255);

            $table->foreign('id_user')
                ->references('id')
                ->on('users');

            $table->index('id_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
