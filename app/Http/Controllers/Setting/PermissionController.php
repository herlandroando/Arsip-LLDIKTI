<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Toast;
use App\Models\Ijin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{

    public function queryAll()
    {
        $this->setTitle("Kelola Ijin");
        $permission = Ijin::all();
        if ($permission->isNotEmpty()) {
            $data = $permission->map(function ($item, $key) {
                return [
                    "id"            => $item->id,
                    "nama"          => $item->nama,
                ];
            });
            $this->setData("list", $data);
            $this->setData("isAvailable", true);
        } else {
            $this->setData("list", []);
            $this->setData("isAvailable", false);
        }
    }
    public function index(Request $request)
    {
        $this->queryAll();
        // dd($user);


        return $this->runInertia("Setting/Permission/Index");
    }

    public function show(Ijin $permission)
    {
        $this->queryAll();

        $this->setData("isSelectedAvailable", true);
        $this->setData("selectedContent", $permission);
        $this->setData("unableChange",$permission->id == 1);

        return $this->runInertia("Setting/Permission/Index");
    }

    public function create()
    {
        $this->queryAll();

        $this->setData("isAdd", true);
        return $this->runInertia("Setting/Permission/Index");
    }

    public function destroy(Ijin $ijin)
    {
        // dd($surat_masuk, "test");
        if ($ijin->id == 1) {
            $toast = Toast::success("Hapus Gagal", "Penghapusan ijin ini tidak diperbolehkan!");
            return $this->redirectInertia(route("setting.permission.index"), $toast);
        }
        $ijin->delete();
        $toast = Toast::success("Hapus Berhasil", "Penghapusan ijin berhasil!");
        return $this->redirectInertia(route("setting.permission.index"), $toast);
    }

    public function store(Request $request)
    {
        $input = $request->input();
        $validator = Validator::make($input, [
            "nama" => "required|string|max:255|min:3",
            "r_surat" => "required|boolean",
            "r_all_disposisi" => "required|boolean",
            "r_laporan" => "required|boolean",
            "d_surat" => "required|boolean",
            "d_miliksurat" => "required|boolean",
            "dp_surat" => "required|boolean",
            "w_disposisi" => "required|boolean",
            "w_suratmasuk" => "required|boolean",
            "w_suratkeluar" => "required|boolean",
            "admin" => "required|boolean",
        ]);
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format pada data form yang dimasukkan.");
            return $this->redirectInertia(route("setting.permission.create"), $toast);
        }

        DB::beginTransaction();
        $ijin                   = new Ijin();
        $ijin->nama             = $input["nama"];
        $ijin->r_surat          = $input["r_surat"];
        $ijin->r_all_disposisi  = $input["r_all_disposisi"];
        $ijin->r_laporan        = $input["r_laporan"];
        $ijin->d_surat          = $input["d_surat"];
        $ijin->d_miliksurat     = $input["d_miliksurat"];
        $ijin->dp_surat         = $input["dp_surat"];
        $ijin->w_disposisi      = $input["w_disposisi"];
        $ijin->w_suratmasuk     = $input["w_suratmasuk"];
        $ijin->w_suratkeluar    = $input["w_suratkeluar"];
        $ijin->admin            = $input["admin"];
        DB::commit();

        $toast = Toast::success("Sukses", "Berhasil menambah ijin!");

        return $this->redirectInertia(route("setting.permission.show", ["permission" => $ijin->id]), $toast);
    }

    public function update(Request $request, Ijin $ijin)
    {
        if ($ijin->id == 1) {
            $toast = Toast::success("Gagal", "Perubahan ijin ini tidak diperbolehkan!");
            return $this->redirectInertia(route("setting.permission.show", ["permission" => $ijin->id]), $toast);
        }
        $input = $request->input();
        $validator = Validator::make($input, [
            "r_surat" => "required|boolean",
            "r_all_disposisi" => "required|boolean",
            "r_laporan" => "required|boolean",
            "d_surat" => "required|boolean",
            "d_miliksurat" => "required|boolean",
            "dp_surat" => "required|boolean",
            "w_disposisi" => "required|boolean",
            "w_suratmasuk" => "required|boolean",
            "w_suratkeluar" => "required|boolean",
            "admin" => "required|boolean",
        ]);
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format pada data form yang dimasukkan.");
            return $this->redirectInertia(route("setting.permission.show", ["permission" => $ijin->id]), $toast);
        }

        DB::beginTransaction();
        $ijin->r_surat          = $input["r_surat"];
        $ijin->r_all_disposisi  = $input["r_all_disposisi"];
        $ijin->r_laporan        = $input["r_laporan"];
        $ijin->d_surat          = $input["d_surat"];
        $ijin->d_miliksurat     = $input["d_miliksurat"];
        $ijin->dp_surat         = $input["dp_surat"];
        $ijin->w_disposisi      = $input["w_disposisi"];
        $ijin->w_suratmasuk     = $input["w_suratmasuk"];
        $ijin->w_suratkeluar    = $input["w_suratkeluar"];
        $ijin->admin            = $input["admin"];
        DB::commit();

        if ($ijin->wasChanged()) {
            $toast = Toast::success("Sukses", "Berhasil menambah ijin!");
        } else {
            $toast = Toast::success("Sukses", "Tidak ada yang berubah.");
        }

        return $this->redirectInertia(route("setting.permission.show", ["permission" => $ijin->id]), $toast);
    }


    // if ($surat_masuk->wasChanged()) {
    //     $toast = Toast::success("Sukses", "Berhasil mengedit surat!");
    // } else {
    //     $toast = Toast::success("Sukses", "Tidak ada yang berubah.");
    // }

}
