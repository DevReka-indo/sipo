<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE risalah MODIFY tgl_dibuat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE risalah MODIFY tgl_dibuat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }
};
