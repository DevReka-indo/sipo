<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_token', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_user', 255);
            $table->string('token', 255);
            $table->string('platform', 45);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_token');
    }
};
