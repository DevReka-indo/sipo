<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section', function (Blueprint $table) {
            $table->increments('id_section');
            $table->string('name_section', 100);
            $table->unsignedInteger('department_id_department');

            $table->foreign('department_id_department')
                ->references('id_department')
                ->on('department')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section');
    }
};
