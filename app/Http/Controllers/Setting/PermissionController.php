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

        $this->setData("unableChange", request()->user()->id != 1);
        return $this->runInertia("Setting/Permission/Index");
    }

    public function show(Ijin $permission)
    {
        $this->queryAll();

        $this->setData("isSelectedAvailable", true);
        $this->setData("selectedContent", $permission);
        $this->setData("unableChange", request()->user()->id != 1);
        return $this->runInertia("Setting/Permission/Index");
    }

    public function create()
    {
        $this->queryAll();

        $this->setData("isAdd", true);
        return $this->runInertia("Setting/Permission/Index");
    }

    public function destroy(Ijin $permission)
    {
        // dd($permission, "test");
        if (request()->user()->id != 1) {
            // if ($permission->id == 1 || $permission->admin == 1 && request()->user()->id != 1) {
            $toast = Toast::success("Hapus Gagal", "Penghapusan ijin ini tidak diperbolehkan!");
            return $this->redirectInertia(route("setting.permission.index"), $toast);
        }
        $permission->delete();
        $toast = Toast::success("Hapus Berhasil", "Penghapusan ijin berhasil!");
        return $this->redirectInertia(route("setting.permission.show", ["permission" => $permission->id]), $toast);
    }

    public function store(Request $request)
    {
        if (request()->user()->id != 1) {
            // if ($permission->id == 1 || $permission->admin == 1 && request()->user()->id != 1) {
            $toast = Toast::success("Hapus Gagal", "Penambahan ijin ini tidak diperbolehkan!");
            return $this->redirectInertia(route("setting.permission.index"), $toast);
        }
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
            "w_all_surat" => "required|boolean",
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
        $ijin->w_all_surat      = $input["w_all_surat"];
        $ijin->w_suratkeluar    = $input["w_suratkeluar"];
        $ijin->admin            = request()->user()->id == 1 ? $input["admin"] : 0;
        $ijin->save();
        DB::commit();

        $toast = Toast::success("Sukses", "Berhasil menambah ijin!");

        return $this->redirectInertia(route("setting.permission.show", ["permission" => $ijin->id]), $toast);
    }

    public function update(Request $request, Ijin $permission)
    {
        if (request()->user()->id != 1) {
            $toast = Toast::success("Gagal", "Perubahan ijin ini tidak diperbolehkan!");
            return $this->redirectInertia(route("setting.permission.show", ["permission" => $permission->id]), $toast);
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
            "w_all_surat" => "required|boolean",
            "w_suratmasuk" => "required|boolean",
            "w_suratkeluar" => "required|boolean",
            "admin" => "required|boolean",
        ]);
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format pada data form yang dimasukkan.");
            return $this->redirectInertia(route("setting.permission.show", ["permission" => $permission->id]), $toast);
        }

        DB::beginTransaction();
        $permission->r_surat          = $input["r_surat"];
        $permission->r_all_disposisi  = $input["r_all_disposisi"];
        $permission->r_laporan        = $input["r_laporan"];
        $permission->d_surat          = $input["d_surat"];
        $permission->d_miliksurat     = $input["d_miliksurat"];
        $permission->dp_surat         = $input["dp_surat"];
        $permission->w_disposisi      = $input["w_disposisi"];
        $permission->w_suratmasuk     = $input["w_suratmasuk"];
        $permission->w_all_surat      = $input["w_all_surat"];
        $permission->w_suratkeluar    = $input["w_suratkeluar"];
        $permission->admin            = request()->user()->id == 1 ? $input["admin"] : 0;
        $permission->save();
        DB::commit();

        if ($permission->wasChanged()) {
            $toast = Toast::success("Sukses", "Berhasil menambah ijin!");
        } else {
            $toast = Toast::success("Sukses", "Tidak ada yang berubah.");
        }

        return $this->redirectInertia(route("setting.permission.show", ["permission" => $permission->id]), $toast);
    }


    // if ($surat_masuk->wasChanged()) {
    //     $toast = Toast::success("Sukses", "Berhasil mengedit surat!");
    // } else {
    //     $toast = Toast::success("Sukses", "Tidak ada yang berubah.");
    // }

}
