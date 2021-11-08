<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->session());
        if (empty($request->user())) {
            $toast = new Toast(Toast::ERROR, "Tidak Diijinkan", "Anda belum masuk sebagai pengguna!");
            return $this->redirectInertia(url("login"), $toast);
        }
        $this->setData("totalSuratMasuk", $this->countTotalData("suratmasuk"));
        $this->setData("totalSuratKeluar", $this->countTotalData("suratkeluar"));
        $this->setData("totalDisposisiAktif", $this->countTotalData("disposisi"));
        $disposisi_aktif = Disposisi::active(10)->get();
        $surat_masuk_baru = SuratMasuk::newMail(10)->get();
        $surat_keluar_baru = SuratKeluar::newMail(10)->get();
        // dd($surat_keluar_baru);
        $this->setData("disposisiAktif", $disposisi_aktif->isNotEmpty() ? $disposisi_aktif : []);
        $this->setData("suratMasukBaru", $surat_masuk_baru->isNotEmpty() ? $surat_masuk_baru : []);
        $this->setData("suratKeluarBaru", $surat_keluar_baru->isNotEmpty() ? $surat_keluar_baru : []);
        $this->setTitle("Dashboard");
        $this->setIndexActive("dashboard");
        return $this->runInertia("Home/Index");
    }

    public function countTotalData($table)
    {
        if ($table=== "disposisi") {
            return DB::table($table)->whereNotIn("status", [Disposisi::BERAKHIR, Disposisi::SELESAI])->count("id");
        } else {
            return DB::table($table)->whereNull("deleted_at")->count("id");
        }
    }
}
