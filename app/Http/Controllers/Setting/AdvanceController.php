<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Toast;
use App\Models\PengaturanUmum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdvanceController extends Controller
{
    public function index()
    {
        $this->setTitle("Kelola Pengaturan Lanjut");
        $data = PengaturanUmum::getCollection(true);
        $this->setData("data", $data);
        return $this->runInertia("Setting/Advance/Index");
    }

    public function update(Request $request)
    {
        $this->setTitle("Kelola Pengaturan Lanjut");
        $input = $request->input();
        $validator = Validator::make($input, [
            "retensi" => "required|numeric|min:2|max:100",
            "autoDeleteRetensiMail" => "required|boolean",
            "deleteMailNotPermanent" => "required|boolean",
        ]);
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format atau duplikasi pada data form yang dimasukkan.");
            return $this->redirectInertia(route("setting.advance.index"), $toast);
        }
        $only = ["retensi", "autoDeleteRetensiMail", "deleteMailNotPermanent"];
        DB::beginTransaction();
        foreach ($only as $value) {
            $key = Str::snake($value);
            PengaturanUmum::setSetting($key, $input[$value]);
        }
        DB::commit();
        $toast = Toast::success("Sukses", "Berhasil ubah pengaturan!");

        return $this->redirectInertia(route("setting.advance.index"), $toast);
    }
}
