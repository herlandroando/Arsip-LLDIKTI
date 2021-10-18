<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Disposisi;
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
        DB::table('pengaturan_umum')->insert([
            ["nama"=>"retensi","nilai"=>"2"],
            ["nama"=>"delete_mail_not_permanent","nilai"=>"true"],
            ["nama"=>"auto_delete_retensi_mail","nilai"=>"false"],
            ["nama"=>"expiration_time_retensi_mail","nilai"=>"0"],
        ]);

        DB::table('ijin')->insert([
            "nama" => "Admin",
            "r_laporan" => 1,
            "r_surat" => 1,
            "r_all_disposisi"=>1,
            "d_surat" => 1,
            "d_miliksurat" => 1,
            "dp_surat" => 1,
            "w_disposisi" => 1,
            "w_suratmasuk" => 1,
            "w_suratkeluar" => 1,
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
            UserSeeder::class,
            SifatSuratSeeder::class,
            SuratKeluarSeeder::class,
            SuratMasukSeeder::class,
            DisposisiSeeder::class
            // TembusanSeeder::class,
        ]);
    }
}
