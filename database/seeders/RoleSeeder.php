<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('role')->insert([
            [
                'id_role' => 1,
                'nm_role' => 'superadmin',
            ],
            [
                'id_role' => 2,
                'nm_role' => 'admin',
            ],
            [
                'id_role' => 3,
                'nm_role' => 'manager',
            ],
        ]);
    }
}
