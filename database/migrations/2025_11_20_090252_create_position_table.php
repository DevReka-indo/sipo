<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('position', function (Blueprint $table) {
            $table->increments('id_position');
            $table->string('nm_position', 45);
            $table->string('kode_position', 45)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('position');
    }
};
