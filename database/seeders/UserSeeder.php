<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'firstname' => 'super',
            'lastname' => 'admin',
            'nip' => '12345678910',
            'email' => 'superadmin@gmail.com',
            'email_verified_at' => null,
            'password' => Hash::make('12345678910'),
            'phone_number' => '1234567890',
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'role_id_role' => 1,
            'position_id_position' => 9,
            'director_id_director' => null,
            'divisi_id_divisi' => null,
            'department_id_department' => null,
            'section_id_section' => null,
            'unit_id_unit' => null,
            'profile_image' => null,
            'deleted_at' => null,
        ]);
    }
}
