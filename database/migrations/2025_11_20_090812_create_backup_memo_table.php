<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_memo', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('document_id');
            $table->timestamp('created_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('updated_at')->nullable();

            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_memo');
    }
};
