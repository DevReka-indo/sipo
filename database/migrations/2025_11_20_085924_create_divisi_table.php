<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisi', function (Blueprint $table) {
            $table->increments('id_divisi');
            $table->unsignedInteger('director_id_director')->nullable();
            $table->string('nm_divisi', 45)->nullable();
            $table->string('kode_divisi', 10)->nullable();

            $table->foreign('director_id_director')
                ->references('id_director')
                ->on('director')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('divisi');
    }
};
