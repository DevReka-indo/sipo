<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit', function (Blueprint $table) {
            $table->increments('id_unit');
            $table->string('name_unit', 100);
            $table->unsignedInteger('department_id_department')->nullable();
            $table->unsignedInteger('section_id_section')->nullable();

            $table->foreign('department_id_department')
                ->references('id_department')
                ->on('department');

            $table->foreign('section_id_section')
                ->references('id_section')
                ->on('section')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit');
    }
};
