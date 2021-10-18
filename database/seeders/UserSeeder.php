<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pengguna')->insert([
            'username' => 'collins.dion',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            // 'remember_token' => \Illuminate\Support\Str::random(10),
            "nama" => "Admin Tertinggi",
            "nip" => "123456789012345678",
            "no_telpon" => "081234567890",
            "id_jabatan" => 1,
        ]);
        DB::table('pengguna')->insert([
            'username' => 'korni.andal',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            // 'remember_token' => \Illuminate\Support\Str::random(10),
            "nama" => "Korni Andal",
            "nip" => "123456789012345671",
            "no_telpon" => "081234567890",
            "id_jabatan" => 2,
        ]);
        DB::table('pengguna')->insert([
            'username' => 'red.andal',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            // 'remember_token' => \Illuminate\Support\Str::random(10),
            "nama" => "Red Andal",
            "nip" => "123456789012345622",
            "no_telpon" => "081234567890",
            "id_jabatan" => 3,
        ]);
        DB::table('pengguna')->insert([
            'username' => 'momo.andal',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            // 'remember_token' => \Illuminate\Support\Str::random(10),
            "nama" => "Momo Andal",
            "nip" => "123456789012315622",
            "no_telpon" => "081234567890",
            "id_jabatan" => 4,
        ]);
        \App\Models\User::factory(4)->create();
    }
}
