<?php

namespace Database\Seeders;

use App\Models\DetailDisposisi;
use App\Models\Disposisi;
use Illuminate\Database\Seeder;

class DisposisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //  $option = ["Sedang Diproses", "Belum Diproses", "Ditinjau", "Revisi", "Selesai", "Berakhir"];

        //Test it original state
        Disposisi::factory()->create();
        //Test it suratmasuk Disposisi state
        Disposisi::factory()->is_suratmasuk()->create();
        //Test it Disposisi state on Sedang Diproses
        $disposisi = Disposisi::factory()->state(["status" => "Sedang Diproses", "id_pengirim" => 4])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 2])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 4])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 2])->has_update_status("Sedang Diproses")->create();
        //Test it Disposisi state on Ditinjau
        $disposisi = Disposisi::factory()->state(["status" => "Ditinjau", "id_pengirim" => 4])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 2])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 4])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 2])->has_update_status("Sedang Diproses")->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 2])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 2])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 4])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 2])->has_update_status("Ditinjau")->create();
        //Test it Disposisi state on Selesai
        $disposisi = Disposisi::factory()->state(["status" => "Selesai", "id_pengirim" => 4])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 2])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 4])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 2])->has_update_status("Sedang Diproses")->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 2])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 2])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 4])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 2])->has_update_status("Ditinjau")->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 2])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 4])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => 2])->has_update_status("Selesai")->create();
        //Test it Disposisi state on Berakhir
        $disposisi = Disposisi::factory()->state(["status" => "Selesai", "id_pengirim" => 4])->create();
        DetailDisposisi::factory()->for($disposisi, "disposisi")->state(["id_pembuat" => null])->has_update_status("Berakhir")->create();
    }
}
