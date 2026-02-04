<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_document', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_document');
            $table->string('jenis_document', 255);
            $table->unsignedInteger('divisi_id_divisi')->nullable();
            $table->timestamp('created_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('divisi_id_divisi')
                ->references('id_divisi')
                ->on('divisi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_document');
    }
};
