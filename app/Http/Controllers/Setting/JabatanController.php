<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Toast;
use App\Models\Ijin;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JabatanController extends Controller
{
    public function queryAll($with_permission = false)
    {
        $this->setTitle("Kelola Jabatan");
        $jabatan = Jabatan::all();
        if ($jabatan->isNotEmpty()) {
            $data = $jabatan->map(function ($item, $key) {
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
        if ($with_permission) {
            if (request()->user()->id == 1)
                $permission = Ijin::all(["id", "nama"]);
            else {
                $permission = Ijin::where("admin", "!=", 1)->get();
            }
            $this->setData("permissionList", $permission->isNotEmpty() ? $permission : []);
        }
    }
    public function index(Request $request)
    {
        $this->queryAll();
        // dd($user);
        $this->setData("unableChange", request()->user()->id != 1);
        return $this->runInertia("Setting/Jabatan/Index");
    }

    public function destroy(Jabatan $jabatan)
    {
        // dd($surat_masuk, "test");
        if (request()->user()->id != 1) {
            $toast = Toast::success("Hapus Gagal", "Penghapusan jabatan ini tidak diperbolehkan!");
            return $this->redirectInertia(route("setting.jabatan.index"), $toast);
        }
        $jabatan->delete();
        $toast = Toast::success("Hapus Berhasil", "Penghapusan jabatan berhasil!");
        return $this->redirectInertia(route("setting.jabatan.index"), $toast);
    }

    public function show(Jabatan $jabatan)
    {
        $this->queryAll(true);

        $this->setData("isSelectedAvailable", true);
        $this->setData("selectedContent", $jabatan);
        $this->setData("unableChange", request()->user()->id != 1);

        return $this->runInertia("Setting/Jabatan/Index");
    }

    public function create()
    {
        $this->queryAll(true);

        $this->setData("isAdd", true);
        return $this->runInertia("Setting/Jabatan/Index");
    }


    public function store(Request $request)
    {
        if (request()->user()->id != 1) {
            $toast = Toast::success("Gagal", "Penambahan jabatan ini tidak diperbolehkan!");
            return $this->redirectInertia(route("setting.jabatan.create"), $toast);
        }
        $input = $request->input();
        $validator = Validator::make($input, [
            "nama" => "required|string|max:255|min:3|unique:jabatan,nama",
            "ijin" => "required|exists:ijin,id",
        ]);
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format atau duplikasi pada data form yang dimasukkan.");
            return $this->redirectInertia(route("setting.jabatan.create"), $toast);
        }
        if (Ijin::find($input["ijin"])->admin == 1 && !request()->user()->id == 1) {
            $toast = Toast::error("Gagal", "Perubahan jabatan ini tidak diperbolehkan!");
            return $this->redirectInertia(route("setting.jabatan.create"), $toast);
        }

        DB::beginTransaction();
        $jabatan                   = new Jabatan();
        $jabatan->nama             = $input["nama"];
        $jabatan->id_ijin          = $input["ijin"];
        $jabatan->save();
        DB::commit();

        $toast = Toast::success("Sukses", "Berhasil menambah jabatan!");

        return $this->redirectInertia(route("setting.jabatan.show", ["jabatan" => $jabatan->id]), $toast);
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        if (request()->user()->id != 1) {
            $toast = Toast::success("Gagal", "Perubahan jabatan ini tidak diperbolehkan!");
            return $this->redirectInertia(route("setting.jabatan.show", ["jabatan" => $jabatan->id]), $toast);
        }
        $input = $request->input();
        $validator = Validator::make($input, [
            "ijin" => "required|exists:ijin,id",
        ]);
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format pada data form yang dimasukkan.");
            return $this->redirectInertia(route("setting.jabatan.show", ["jabatan" => $jabatan->id]), $toast);
        }
        if (Ijin::find($input["ijin"])->admin == 1 && !request()->user()->id == 1) {
            $toast = Toast::error("Gagal", "Perubahan jabatan ini tidak diperbolehkan!");
            return $this->redirectInertia(route("setting.jabatan.show", ["jabatan" => $jabatan->id]), $toast);
        }

        DB::beginTransaction();
        $jabatan->id_ijin   = $input["ijin"];
        $jabatan->save();
        DB::commit();

        if ($jabatan->wasChanged()) {
            $toast = Toast::success("Sukses", "Berhasil menambah jabatan!");
        } else {
            $toast = Toast::success("Sukses", "Tidak ada yang berubah.");
        }

        return $this->redirectInertia(route("setting.jabatan.show", ["jabatan" => $jabatan->id]), $toast);
    }
}
