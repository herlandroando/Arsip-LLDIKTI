<?php

namespace App\Http\Controllers\ManageMail;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Toast;
use App\Models\DokumenSuratKeluar;
use App\Models\PengaturanUmum;
use App\Models\SuratKeluar;
use App\Models\User;
use App\Traits\HasGetFilterList;
use App\Traits\HasManageableTableQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SendController extends Controller
{
    use HasGetFilterList, HasManageableTableQuery;

    protected string $table_name = "suratkeluar";

    protected array $filter_list_option = [
        "sifat" => ["table" => "sifatsurat", "only" => ["nama", "id"]],
        "pembuat" => ["table" => "suratkeluar", "column" => 'nama_pembuat', "distinct" => true],
        "asal_surat" => ["table" => "suratkeluar", "distinct" => true, "column" => 'nama', "relation" => "jabatan.asal_surat", "only" => ["jabatan.nama", "jabatan.id"]],
    ];

    protected array $table_option = [
        "sifat" => ["column" => "id_sifat", "label" => "Sifat Surat", "relation" => "sifatsurat"],
        "pembuat" => ["column" => "nama_pembuat", "label" => "Pembuat Surat"],
        "asal_surat" => ["column" => "asal_surat", "label" => "Asal Surat", "relation" => "jabatan"],
        "tanggal_surat" => ["column" => "tanggal_surat", "label" => "Tanggal Surat"],
        "no_surat" => ["column" => "no_surat", "label" => "No. Surat"],
    ];

    protected array $filter_option = [
        "sifat" => ["type" => "select", "label_column" => "sifatsurat.nama"],
        "pembuat" => ["type" => "select"],
        "asal_surat" => ["type" => "select", "label_column" => "jabatan.nama"],
        "tanggal_surat" => ["type" => "date"],
    ];

    protected array $sort_available = [
        "sifat" => "nama", "pembuat" => "nama_pembuat", "asal_surat" => "nama", "tanggal_surat" => "tanggal_surat", "no_surat" => "no_surat"
    ];

    public function __construct()
    {
        parent::__construct();
        $this->setIndexActive("manage_mail.send");
    }


    public function queryIndex($inertia_request = true)
    {
        // $retensi        = PengaturanUmum::getSetting("retensi");
        // $retensi        = now()->subYears($retensi);
        $search         = request()->query("search");
        $surat_keluar   = SuratKeluar::query();
        // $surat_masuk    = $surat_keluar->where("created_at", ">=", $retensi);

        // dd($surat_masuk);
        if (!empty($search)) {
            $surat_keluar->orWhere("asal_surat", "like", "%$search%")->orWhere("no_surat", "like", "%$search%")->orWhere("perihal", "like", "%$search%");
            $this->setData("q_search", $search);
        }
        $filter = $this->filterQuery($surat_keluar);
        $sort = $this->sortQuery($surat_keluar, true);
        if ($inertia_request) {
            $this->setData("q_filter", $filter["list_filter"]);
            $this->setData("q_filter_tag", $filter["list_tag"]);
            // dd($sort);
            if (!empty($sort)) {
                $this->setData("q_sort", $sort);
            }
        }
        return $surat_keluar;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $page        = $request->query("page", 1);
        $surat_keluar = $this->queryIndex();
        $surat_keluar = $surat_keluar->paginate(10);
        // dd($surat_keluar);
        if ($surat_keluar->isNotEmpty()) {
            $this->setPaginate($surat_keluar);
            $data = $surat_keluar->map(function ($item, $key) {
                return [
                    "id"            => $item->id,
                    "id_pembuat"    => $item->id_pembuat ?? 0,
                    "no_surat"      => $item->no_surat,
                    "sifat"         => $item->sifatSurat->nama,
                    "asal_surat"    => $item->jabatan->nama,
                    "perihal"       => $item->perihal,
                    "tanggal_surat" => Carbon::parse($item->tanggal_surat)->toString(),
                    "pembuat"       => $item->nama_pembuat,
                ];
            });
            $this->setData("deleteNotPermanent", PengaturanUmum::getSetting("delete_mail_not_permanent"));
            $this->setData("tableData", $data);
            $this->setData("isAvailable", true);
        } else {
            $this->setData("isAvailable", false);
        }

        $this->setTitle("Kelola Surat - Surat Keluar");

        return $this->runInertia("ManageMail/Send/Index");
    }

    public function jsonIndex()
    {
        $result = [
            "result" => [],
            // "currentPage"=>0,
            // "lastPage"=>0,
            "empty" => false,
            "limit" => false,
        ];
        $surat_keluar = $this->queryIndex(false);
        $surat_keluar = $surat_keluar->paginate(10);

        if ($surat_keluar->isEmpty()) {
            $result["empty"] = true;
        }
        $result["lastPage"]     = $surat_keluar->lastPage();
        $result["currentPage"]  = $surat_keluar->currentPage();

        if ($result["currentPage"] > $result["lastPage"]) {
            $result["limit"] = true;
            $result["empty"] = false;
        }
        $data = $surat_keluar->map(function ($item, $key) {
            return [
                "id"            => $item->id,
                "id_pembuat"    => $item->id_pembuat ?? 0,
                "no_surat"      => $item->no_surat,
                "sifat"         => $item->sifatSurat->nama,
                "asal_surat"    => $item->jabatan->nama,
                "perihal"       => $item->perihal,
                "tanggal_surat" => Carbon::parse($item->tanggal_surat)->toString(),
                "pembuat"       => $item->nama_pembuat,
            ];
        });
        $result["result"] = $data;
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->setTitle("Buat Surat Keluar");

        if ($request->session()->has("temp_file"))
            $this->setData("tempFileSurat", session("temp_file"));
        return $this->runInertia("ManageMail/Send/Create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $valid_user = $request->user()->jabatan->ijin->w_suratkeluar;
        $valid_admin = $request->user()->jabatan->ijin->w_suratkeluar && $request->user()->jabatan->ijin->admin;
        if (!$valid_user && !$valid_admin) {
            $toast = Toast::error("Gagal", "Anda tidak diperbolehkan melakukan tindakan ini.");
            return $this->redirectInertia(route("manage.send.show"), $toast);
        }

        $input = $request->input();
        $validator = Validator::make($input, [
            "perihal"       => "required|string|max:254",
            "tanggal_surat" => "required|date|before_or_equal:now",
            "id_sifat"      => "required|exists:sifatsurat,id|max:500",
            "pembuat"       => "nullable|string|max:500",
            "no_surat"      => "required|string|max:100",
            "asal_surat"    => "required|exists:jabatan,id",
            "tujuan"        => "required|string|max:255",
            "isi_ringkas" => "nullable|string|max:500",
        ]);
        if ($validator->fails()) {
            // dd($validator->failed());
            $toast = Toast::error("Gagal", "Terjadi kesalahan format pada data form yang dimasukkan.");
            return $this->redirectInertia(route("manage.send.create"), $toast);
        }
        DB::beginTransaction();
        $surat_keluar                = new SuratKeluar;
        $surat_keluar->perihal       = $input["perihal"];
        $surat_keluar->tanggal_surat = Carbon::parse($input["tanggal_surat"]);
        $surat_keluar->no_surat      = $input["no_surat"];
        $surat_keluar->asal_surat    = $input["asal_surat"];
        $surat_keluar->tujuan        = $input["tujuan"];
        $surat_keluar->nama_pembuat  = $input["pembuat"] ?? $request->user()->username;
        $surat_keluar->id_pembuat    = $request->user()->id ?? null;
        $surat_keluar->isi_ringkas   = $input["isi_ringkas"];
        $surat_keluar->id_sifat      = $input["id_sifat"];
        $surat_keluar->save();
        $temp_file = session("temp_file");
        if (!empty($temp_file)) {
            foreach ($temp_file as $file) {
                $part_name_file                     = explode(".", $file["id"]);
                $extension                          = end($part_name_file) ?? "unknown";
                $path_file                          = "temp/{$file['id']}";
                $target_path                        = "dokumen_sk/{$surat_keluar->id}/";
                $file_name                          = Str::random(18) . "." . $extension;

                $file_surat_keluar                   = new DokumenSuratKeluar();
                $file_surat_keluar->ukuran           = Storage::size($path_file);
                $file_surat_keluar->alias            = $file_name;
                $file_surat_keluar->tipe             = $extension;
                $file_surat_keluar->id_suratkeluar   = $surat_keluar->id;
                $file_surat_keluar->nama             = $file["name"] ?? "FileTidakBernama.{$extension}";
                $file_surat_keluar->save();
                Storage::move($path_file, $target_path . $file_name);
            }
        }
        DB::commit();

        $toast = Toast::success("Sukses", "Berhasil menambahkan surat!");

        session(["temp_file" => null]);

        return $this->redirectInertia(route("manage.send.show", ["surat_keluar" => $surat_keluar->id]), $toast);

        // perihal: "",
        // tanggal_surat: "",
        // no_surat: "",
        // asal_surat: "",
        // isi_ringkas: "",
        // id_sifat: "",

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(SuratKeluar $surat_keluar)
    {
        $username = request()->user()->username ?? null;
        $pembuat = User::where("username", $surat_keluar->nama_pembuat)->first();
        $nama_pembuat = $surat_keluar->nama_pembuat;
        $is_creator_me = false;
        if (!empty($pembuat)) {
            $username_pembuat = $pembuat->username;
            $nama_pembuat = $pembuat->nama;  //TODO// Make profile and get name
            $is_creator_me = $pembuat->username == $username;
        }
        $data = [
            "id" => $surat_keluar->id,
            "id_pembuat" => $surat_keluar->id_pembuat,
            "perihal" => $surat_keluar->perihal,
            "tanggal_surat" => Carbon::parse($surat_keluar->tanggal_surat)->toString(),
            "no_surat" => $surat_keluar->no_surat,
            "tujuan" => $surat_keluar->tujuan,
            "asal_surat" => $surat_keluar->asal_surat ?? null,
            "isi_ringkas" => $surat_keluar->isi_ringkas,
            "id_sifat" => $surat_keluar->id_sifat,
            "sifat" => $surat_keluar->sifatSurat->nama,
            "file_surat" => $surat_keluar->dokumen->isEmpty() ? null : $surat_keluar->dokumen->map(
                function ($item, $key) {
                    return [
                        "id" => $item->id,
                        "nama" => $item->nama,
                        "ukuran" => $item->ukuran,
                        "tipe" => $item->tipe,
                        "url" => route("manage.send.download", ["id" => $item->id, "name" => $item->alias])
                    ];
                }
            ),
            "file_surat_form" => $surat_keluar->dokumen->map(function ($item, $key) {
                return [
                    "id" => $item->id,
                    "name" => $item->nama,
                ];
            }),
            "pembuat" => $username_pembuat ?? $nama_pembuat,
            "nama_pembuat" => $nama_pembuat,
            "pembuatnya_saya" =>  $is_creator_me,
            "submit_oleh" => $surat_keluar->user->submit_oleh ?? "Akun Terhapus",
            "bagian_asal_surat" => $surat_keluar->jabatan->nama ?? "Tidak Diketahui",
            "dibuat_tanggal" => Carbon::parse($surat_keluar->created_at)->toString(),
        ];

        $this->setData("deleteNotPermanent", PengaturanUmum::getSetting("delete_mail_not_permanent"));
        $this->setTitle("Detail Surat Keluar", true);
        $this->setData("detailData", $data);
        return $this->runInertia("ManageMail/Send/Show");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SuratKeluar $surat_keluar)
    {
        $valid_user_created = $request->user()->jabatan->ijin->w_suratkeluar && $request->user()->id == $surat_keluar->id_pembuat;
        $valid_admin = $request->user()->jabatan->ijin->admin;
        if (!$valid_user_created && !$valid_admin) {
            $toast = Toast::error("Gagal", "Anda tidak diperbolehkan melakukan tindakan ini.");
            return $this->redirectInertia(route("manage.send.show", ["surat_keluar" => $surat_keluar->id]), $toast);
        }
        $input = $request->input();
        $validator = Validator::make($input, [
            "perihal"       => "required|string|max:254",
            "tanggal_surat" => "required|date|before_or_equal:now",
            "id_sifat"      => "required|exists:sifatsurat,id|max:500",
            "pembuat"       => "nullable|string|max:500",
            "no_surat"      => "required|string|max:100",
            "asal_surat"    => "required|exists:jabatan,id",
            "tujuan"        => "required|string|max:255",
            "isi_ringkas" => "nullable|string|max:500",
        ]);
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format pada data form yang dimasukkan.");
            return $this->redirectInertia(route("manage.send.show", ["surat_keluar" => $surat_keluar->id]), $toast);
        }

        DB::beginTransaction();
        $surat_keluar->perihal       = $input["perihal"];
        $surat_keluar->tanggal_surat = Carbon::parse($input["tanggal_surat"]);
        $surat_keluar->no_surat      = $input["no_surat"];
        $surat_keluar->nama_pembuat  = $input["pembuat"];
        $surat_keluar->asal_surat    = $input["asal_surat"];
        $surat_keluar->isi_ringkas   = $input["isi_ringkas"];
        $surat_keluar->id_sifat      = $input["id_sifat"];
        $surat_keluar->save();
        DB::commit();
        if ($surat_keluar->wasChanged()) {
            $toast = Toast::success("Sukses", "Berhasil mengedit surat!");
        } else {
            $toast = Toast::success("Sukses", "Tidak ada yang berubah.");
        }

        return $this->redirectInertia(route("manage.send.show", ["surat_keluar" => $surat_keluar->id]), $toast);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(SuratKeluar $surat_keluar)
    {
        $is_delete_permanent = PengaturanUmum::getSetting("delete_mail_not_permanent");
        if ($is_delete_permanent) {
            $valid_user_deleted = request()->user()->jabatan->ijin->dp_surat;
            if (!$valid_user_deleted) {
                $toast = Toast::error("Gagal", "Anda tidak diperbolehkan melakukan tindakan ini.");
                return $this->redirectInertia(route("manage.send.index"), $toast);
            }
            $surat_keluar->forceDelete();
        } else {
            $valid_user_deleted = request()->user()->jabatan->ijin->d_surat || (request()->user()->jabatan->ijin->d_miliksurat && request()->user()->id == $surat_masuk->id_pembuat);
            if (!$valid_user_deleted) {
                $toast = Toast::error("Gagal", "Anda tidak diperbolehkan melakukan tindakan ini.");
                return $this->redirectInertia(route("manage.send.index"), $toast);
            }
            $surat_keluar->delete();
        }
        // dd($surat_keluar, "test");
        $toast = Toast::success("Hapus Surat Keluar", "Penghapusan surat keluar berhasil!");
        return $this->redirectInertia(route("manage.send.index"), $toast);
    }

    public function deleteTemp(Request $request)
    {
        $session_temp_file = session("temp_file");
        $id = $request->input("id");
        if (empty($session_temp_file) || empty($id)) {
            return response("failed.not_found", Response::HTTP_BAD_REQUEST);
        }
        $test = "";
        $filtered = array_filter($session_temp_file, function ($value) use ($id, &$test) {
            $test .= $value["id"] . "!=" . $id . "\n";
            return $value["id"] != $id;
        });
        session(["temp_file" => $filtered]);
        return response("success", Response::HTTP_OK);;
    }
    /**
     * Handle upload file to save at temporary.
     *
     * @param Request $request
     * @return void
     */
    public function uploadFile(Request $request, SuratKeluar $surat_keluar)
    {
        $file = $request->file("file");
        if (empty($file)) {
            return response()->json(["success" => false, "message" => "File is null."], Response::HTTP_BAD_REQUEST);
        }
        $valid_user_created = $request->user()->jabatan->ijin->w_suratkeluar && $request->user()->id == $surat_keluar->id_pembuat;
        $valid_admin = $request->user()->jabatan->ijin->w_suratkeluar && $request->user()->jabatan->ijin->admin;
        if (!$valid_user_created && !$valid_admin) {
            return response("failed.validation_user", Response::HTTP_BAD_REQUEST);
        }
        $validator = Validator::make(["files" => $file], [
            "files" => "required|file|max:4096|mimes:png,jpg,jpeg,gif,bmp,pdf"
        ]);
        if ($validator->fails()) {
            return response("failed.validation", Response::HTTP_BAD_REQUEST);
        }

        $id_surat                           = $surat_keluar->id;
        $extension                          = $file->getClientOriginalExtension();
        $file_name                          = Str::random(18) . "." . $extension;
        $file->storeAs("dokumen_sk/$id_surat", $file_name);

        $file_surat_keluar                   = new DokumenSuratKeluar;
        $file_surat_keluar->ukuran           = $file->getSize();
        $file_surat_keluar->alias            = $file_name;
        $file_surat_keluar->tipe             = $extension;
        $file_surat_keluar->id_suratkeluar    = $surat_keluar->id;
        $file_surat_keluar->nama             = $file->getClientOriginalName() ?? "FileTidakBernama.{$extension}";
        $file_surat_keluar->save();

        return response()->json([
            "fileSuratForm" => [
                "name" => $file->getClientOriginalName(),
                "id" => $file_surat_keluar->id
            ], "fileSurat" => [
                "id" => $file_surat_keluar->id,
                "nama" => $file_surat_keluar->nama,
                "ukuran" => $file_surat_keluar->ukuran,
                "tipe" => $file_surat_keluar->tipe,
                "url" => route("manage.send.download", ["id" => $file_surat_keluar->id, "name" => $file_surat_keluar->alias])
            ]
        ]);
    }

    public function deleteFile(Request $request, SuratKeluar $surat_keluar)
    {
        $valid_user_created = $request->user()->jabatan->ijin->w_suratkeluar && $request->user()->id == $surat_keluar->id_pembuat;
        $valid_admin = $request->user()->jabatan->ijin->w_suratkeluar && $request->user()->jabatan->ijin->admin;
        if (!$valid_user_created && !$valid_admin) {
            return response("failed", Response::HTTP_BAD_REQUEST);
        }
        $id_surat = $surat_keluar->id;
        $path = "dokumen_sk/$id_surat/";
        $id_file = $request->input("id");
        if (empty($id_file)) {
            return response("failed.not_found", Response::HTTP_BAD_REQUEST);
        }
        $dokumen = DokumenSuratKeluar::where("id_suratkeluar", $id_surat)->where("id", $id_file)->first();
        if (empty($dokumen))
            return response("failed.not_found_dokumen", Response::HTTP_BAD_REQUEST);
        $file_name = $dokumen->alias;
        $full_path = $path . $file_name;
        if (Storage::exists($full_path))
            Storage::delete($full_path);
        $dokumen->delete();
        return response("success", Response::HTTP_OK);
    }
    /**
     * Handle upload file to save at temporary.
     *
     * @param Request $request
     * @return void
     */
    public function uploadTemp(Request $request)
    {
        $valid_user = $request->user()->jabatan->ijin->w_suratkeluar;
        $valid_admin = $request->user()->jabatan->ijin->w_suratkeluar && $request->user()->jabatan->ijin->admin;
        if (!$valid_user && !$valid_admin) {
            return response("failed", Response::HTTP_BAD_REQUEST);
        }
        $file = $request->file("file");
        $validator = Validator::make(["files" => $file], [
            "files" => "required|file|max:4096|mimes:png,jpg,jpeg,gif,bmp,pdf"
        ]);
        if ($validator->fails()) {
            return response("failed.validation", Response::HTTP_BAD_REQUEST);
        }
        $date = now()->startOfDay()->timestamp;
        $file_name = Str::random(4) . (string)$date . ".{$file->extension()}";
        $store_session_name =  "$date/$file_name";
        $file->storeAs("temp/$date", $file_name);
        $temp_file = session("temp_file") ?? [];
        array_push($temp_file, ["name" => $file->getClientOriginalName(), "id" => $store_session_name]);
        session(["temp_file" => $temp_file]);

        return response()->json(["name" => $file->getClientOriginalName(), "id" => $store_session_name]);
    }

    public function searchUsername(Request $request)
    {
        $search = $request->query("search");
        $users = User::orWhere("username", "like", "%" . $search . "%")
            ->orWhere("nama", "like", "%" . $search . "%")
            ->orWhere("nip", "like", "%" . $search . "%")->limit(7)->get(); // TODO// Add search by profile name
        // dd($users->get(),$users->toSql());
        $result = $users->map(function ($item, $key) {
            return ["value" => "$item->nama ($item->username)", "real_value" => $item->username];
        });
        return response()->json(["result" => $result]);
    }

    public function download(Request $request, $id, $name)
    {
        // gambar: ["png", "jpg", "jpeg", "gif", "bmp"],
        // pdf
        if (!$request->user()->jabatan->ijin->r_surat && !$request->user()->jabatan->ijin->admin) {
            return $this->throwOrRedirect("Not Permitted to download.", 404);
        }
        $open = $request->query("open", 0);

        $dokumen = DB::table('dokumen_sk')->where("id", $id)->where("alias", $name)->first();
        if (empty($dokumen)) {
            return $this->throwOrRedirect("File is not found.", 404);
        }
        // dd($laporan,Storage::exists("reports/$laporan->path"));
        if (Storage::exists("dokumen_sk/$dokumen->id_suratkeluar/$dokumen->alias")) {
            if (in_array($dokumen->tipe, ["pdf", "jpg", "png", "jpeg", "gif", "bmp"]) && $open == 1)
                return response()->file(storage_path("app/dokumen_sk/$dokumen->id_suratkeluar/$dokumen->alias"));
            else {
                return response()->download(storage_path("app/dokumen_sk/$dokumen->id_suratkeluar/$dokumen->alias", $dokumen->alias));
            }
        } else {
            return $this->redirectInertia(route("errors.index", ["code" => 404]));
        }
    }
}
