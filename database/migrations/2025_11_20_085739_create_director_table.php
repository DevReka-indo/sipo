<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('director', function (Blueprint $table) {
            $table->increments('id_director');
            $table->string('name_director', 100)->nullable();
            $table->string('kode_director', 10);
            $table->boolean('is_main')->default(false);
            $table->unsignedInteger('parent_director_id')->nullable();

            $table->foreign('parent_director_id')
                ->references('id_director')
                ->on('director')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('director');
    }
};
