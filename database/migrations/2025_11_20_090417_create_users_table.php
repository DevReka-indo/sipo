<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('firstname', 50);
            $table->string('lastname', 50)->nullable();
            $table->string('nip');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone_number', 50);
            $table->rememberToken();
            $table->timestamps();

            $table->unsignedInteger('role_id_role');
            $table->unsignedInteger('position_id_position');
            $table->unsignedInteger('director_id_director')->nullable();
            $table->unsignedInteger('divisi_id_divisi')->nullable();
            $table->unsignedInteger('department_id_department')->nullable();
            $table->unsignedInteger('section_id_section')->nullable();
            $table->unsignedInteger('unit_id_unit')->nullable();
            $table->longText('profile_image')->nullable();
            $table->softDeletes();

            $table->foreign('role_id_role')
                ->references('id_role')
                ->on('role');

            $table->foreign('position_id_position')
                ->references('id_position')
                ->on('position');

            $table->foreign('director_id_director')
                ->references('id_director')
                ->on('director')
                ->nullOnDelete();

            $table->foreign('divisi_id_divisi')
                ->references('id_divisi')
                ->on('divisi')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('department_id_department')
                ->references('id_department')
                ->on('department')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('section_id_section')
                ->references('id_section')
                ->on('section')
                ->nullOnDelete();

            $table->foreign('unit_id_unit')
                ->references('id_unit')
                ->on('unit')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
