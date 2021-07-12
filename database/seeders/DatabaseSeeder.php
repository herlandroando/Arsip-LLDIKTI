<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pengguna')->insert([
            'username' => 'collins.dion',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);
        \App\Models\User::factory(4)->create();
        DB::table('ijin')->insert([
            "nama" => "Admin",
            "r_laporan" => 1,
            "r_surat" => 1,
            "d_surat" => 1,
            "d_miliksurat" => 1,
            "dp_surat" => 1,
            "w_disposisi" => 1,
            "w_surat" => 1,
            "admin" => 1
        ]);
        DB::table('jabatan')->insert([
            [
                "nama" => "Administrator",
                "id_ijin" => 1,
            ],
            [
                "nama" => "Sekertaris",
                "id_ijin" => 1,
            ],
            [
                "nama" => "TU",
                "id_ijin" => 1,
            ],
            [
                "nama" => "Pimpinan",
                "id_ijin" => 1,
            ],
        ]);

        $this->call([
            SifatSuratSeeder::class,
            SuratKeluarSeeder::class,
            SuratMasukSeeder::class,
            TembusanSeeder::class,
        ]);
    }
}
