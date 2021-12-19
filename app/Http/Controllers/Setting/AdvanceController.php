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
        $this->setTitle("Pengaturan Lanjutan");
        $data = PengaturanUmum::getCollection(true);
        $this->setData("data", $data);
        return $this->runInertia("Setting/Advance/Index");
    }

    public function update(Request $request)
    {
        $input = $request->input();
        $validator = Validator::make($input, [
            // "retensi" => "required|numeric|min:2|max:100",
            // "autoDeleteRetensiMail" => "required|boolean", "retensi", "autoDeleteRetensiMail",
            "deleteMailNotPermanent" => "required|boolean",
        ]);
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format atau duplikasi pada data form yang dimasukkan.");
            return $this->redirectInertia(route("setting.advance.index"), $toast);
        }
        $only = ["deleteMailNotPermanent" => "boolean"];
        DB::beginTransaction();
        foreach ($only as $key => $value) {
            $key_snake = Str::snake($key);
            if ($value == 'boolean')
                $input[$key] = $input[$key] ? 'true' : 'false';
            PengaturanUmum::setSetting($key_snake, $input[$key]);
        }
        DB::commit();
        $toast = Toast::success("Sukses", "Berhasil ubah pengaturan!");

        return $this->redirectInertia(route("setting.advance.index"), $toast);
    }
}
