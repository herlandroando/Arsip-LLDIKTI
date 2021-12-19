<?php

namespace App\Http\Controllers\ManageMail;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Toast;
use App\Http\Requests\StoreSuratMasukRequest;
use App\Models\DokumenSuratMasuk;
use App\Models\PengaturanUmum;
use App\Models\SuratMasuk;
use App\Traits\HasManageableTableQuery;
use App\Traits\HasGetFilterList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InboxController extends Controller
{
    use HasGetFilterList, HasManageableTableQuery;

    protected string $table_name = "suratmasuk";

    protected array $filter_list_option = [
        "sifat" => ["table" => "sifatsurat", "only" => ["nama", "id"]],
        "pembuat" => ["table" => "suratmasuk", "relation" => "pengguna.id_pembuat", "column" => 'nama', "only" => ["pengguna.nama", "pengguna.id"]],
        "asal_surat" => ["table" => "suratmasuk", "column" => 'asal_surat', "distinct" => true],
    ];

    protected array $table_option = [
        "sifat" => ["column" => "id_sifat", "label" => "Sifat Surat", "relation" => "sifatsurat"],
        "pembuat" => ["column" => "id_pembuat", "label" => "Pembuat Surat", "relation" => "pengguna"],
        "asal_surat" => ["column" => "asal_surat", "label" => "Asal Surat"],
        "tanggal_surat" => ["column" => "tanggal_surat", "label" => "Tanggal Surat"],
        "no_surat" => ["column" => "no_surat", "label" => "No. Surat"],
    ];

    protected array $filter_option = [
        "sifat" => ["type" => "select", "label_column" => "sifatsurat.nama"],
        "pembuat" => ["type" => "select", "label_column" => "pengguna.nama"],
        "asal_surat" => ["type" => "select"],
        "tanggal_surat" => ["type" => "date"],
    ];

    protected array $sort_available = [
        "sifat" => "nama", "pembuat" => "nama", "asal_surat" => "asal_surat", "tanggal_surat" => "tanggal_surat", "no_surat" => "no_surat"
    ];

    public function __construct()
    {
        parent::__construct();
        $this->setIndexActive("manage_mail.inbox");
    }

    public function queryIndex($inertia_request = true)
    {
        // $retensi        = PengaturanUmum::getSetting("retensi");
        // $retensi        = now()->subYears($retensi);
        $search         = request()->query("search");
        $surat_masuk    = SuratMasuk::query();
        // $surat_masuk    = $surat_masuk->where("created_at", ">=", $retensi);
        // dd($surat_masuk);
        if (!empty($search)) {
            $surat_masuk->orWhere($this->table_name . ".asal_surat", "like", "%$search%")->orWhere($this->table_name . ".no_surat", "like", "%$search%")->orWhere($this->table_name . ".perihal", "like", "%$search%");
            $this->setData("q_search", $search);
        }
        $filter = $this->filterQuery($surat_masuk);
        $sort = $this->sortQuery($surat_masuk, true);
        if ($inertia_request) {
            $this->setData("q_filter", $filter["list_filter"]);
            $this->setData("q_filter_tag", $filter["list_tag"]);
            // dd($sort);
            if (!empty($sort)) {
                $this->setData("q_sort", $sort);
            }
        }
        return $surat_masuk;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // $page        = $request->query("page", 1);
        $surat_masuk = $this->queryIndex();

        // dd("-");
        // $query = str_replace(array('?'), array('\'%s\''), $surat_masuk->toSql());
        // $query = vsprintf($query, $surat_masuk->getBindings());
        // dd($query, $this->getSendedData());
        $surat_masuk = $surat_masuk->paginate(10);
        // dd($surat_masuk);
        if ($surat_masuk->isNotEmpty()) {
            $this->setPaginate($surat_masuk);
            $data = $surat_masuk->map(function ($item, $key) {
                return [
                    "id"            => $item->id,
                    "id_pembuat"    => $item->id_pembuat ?? 0,
                    "no_surat"      => $item->no_surat,
                    "sifat"         => $item->sifatSurat->nama,
                    "asal_surat"    => $item->asal_surat,
                    "perihal"       => $item->perihal,
                    "tanggal_surat" => Carbon::parse($item->tanggal_surat)->toString(),
                    "no_agenda"     => $item->no_agenda,
                ];
            });
            $this->setData("tableData", $data);
            $this->setData("deleteNotPermanent", PengaturanUmum::getSetting("delete_mail_not_permanent"));
            $this->setData("isAvailable", true);
        } else {
            $this->setData("isAvailable", false);
        }
        // dd($data);
        $this->setTitle("Kelola Surat - Surat Masuk");

        return $this->runInertia("ManageMail/Inbox/Index");
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
        $surat_masuk = $this->queryIndex(false);
        $surat_masuk = $surat_masuk->paginate(10);

        if ($surat_masuk->isEmpty()) {
            $result["empty"] = true;
        }
        $result["lastPage"]     = $surat_masuk->lastPage();
        $result["currentPage"]  = $surat_masuk->currentPage();

        if ($result["currentPage"] > $result["lastPage"]) {
            $result["limit"] = true;
            $result["empty"] = false;
        }
        $data = $surat_masuk->map(function ($item, $key) {
            return [
                "id"            => $item->id,
                "id_pembuat"    => $item->id_pembuat ?? 0,
                "no_surat"      => $item->no_surat,
                "sifat"         => $item->sifatSurat->nama,
                "asal_surat"    => $item->asal_surat,
                "perihal"       => $item->perihal,
                "tanggal_surat" => Carbon::parse($item->tanggal_surat)->toString(),
                "no_agenda"     => $item->no_agenda,
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
        // session(["temp_file" => []]);
        // 1625788800/AO0O1625788800.jpg
        $this->setTitle("Buat Surat Masuk", true);
        // session(["temp_file"=>null]);
        // dd(session("temp_file"));
        if ($request->session()->has("temp_file"))
            $this->setData("tempFileSurat", session("temp_file"));
        return $this->runInertia("ManageMail/Inbox/Create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $valid_user = $request->user()->jabatan->ijin->w_suratmasuk;
        $valid_admin = $request->user()->jabatan->ijin->w_suratmasuk && $request->user()->jabatan->ijin->admin;
        if (!$valid_user && !$valid_admin) {
            $toast = Toast::error("Gagal", "Anda tidak diperbolehkan melakukan tindakan ini.");
            return $this->redirectInertia(route("manage.inbox.show"), $toast);
        }
        $input = $request->input();
        $validator = Validator::make($input, [
            "perihal" => "required|string|max:254",
            "tanggal_surat" => "required|date|before_or_equal:now",
            "id_sifat" => "required|exists:sifatsurat,id|max:500",
            "no_surat" => "required|string|max:100",
            "no_agenda" => "required|string|max:100",
            "asal_surat" => "required|string|max:500",
            "isi_ringkas" => "nullable|string|max:500",
        ]);
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format pada data form yang dimasukkan.");
            return $this->redirectInertia(route("manage.inbox.create"), $toast);
        }
        DB::beginTransaction();
        $surat_masuk                = new SuratMasuk;
        $surat_masuk->perihal       = $input["perihal"];
        $surat_masuk->tanggal_surat = Carbon::parse($input["tanggal_surat"]);
        $surat_masuk->no_surat      = $input["no_surat"];
        $surat_masuk->no_agenda     = $input["no_agenda"];
        $surat_masuk->asal_surat    = $input["asal_surat"];
        $surat_masuk->isi_ringkas   = $input["isi_ringkas"];
        $surat_masuk->id_sifat      = $input["id_sifat"];
        $surat_masuk->id_pembuat    = $request->user()->id ?? null;
        $surat_masuk->save();
        $temp_file = session("temp_file");
        if (!empty($temp_file)) {
            foreach ($temp_file as $file) {
                $part_name_file                     = explode(".", $file["id"]);
                $extension                          = end($part_name_file) ?? "unknown";
                $path_file                          = "temp/{$file['id']}";
                $target_path                        = "dokumen_sm/{$surat_masuk->id}/";
                $file_name                          = Str::random(18) . "." . $extension;

                $file_surat_masuk                   = new DokumenSuratMasuk;
                $file_surat_masuk->ukuran           = Storage::size($path_file);
                $file_surat_masuk->alias            = $file_name;
                $file_surat_masuk->tipe             = $extension;
                $file_surat_masuk->id_suratmasuk    = $surat_masuk->id;
                $file_surat_masuk->nama             = $file["name"] ?? "FileTidakBernama.{$extension}";
                $file_surat_masuk->save();
                Storage::move($path_file, $target_path . $file_name);
            }
        }
        DB::commit();

        $toast = Toast::success("Sukses", "Berhasil menambahkan surat!");

        session(["temp_file" => null]);

        return $this->redirectInertia(route("manage.inbox.show", ["surat_masuk" => $surat_masuk->id]), $toast);
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
    public function show(SuratMasuk $surat_masuk)
    {
        // dd($surat_masuk->created_at);
        // dd($surat_masuk->user, $surat_masuk);
        $data = [
            "id"                => $surat_masuk->id,
            "id_pembuat"        => $surat_masuk->id_pembuat,
            "perihal"           => $surat_masuk->perihal,
            "tanggal_surat"     => Carbon::parse($surat_masuk->tanggal_surat)->toString(),
            "no_surat"          => $surat_masuk->no_surat,
            "asal_surat"        => $surat_masuk->asal_surat,
            "isi_ringkas"       => $surat_masuk->isi_ringkas,
            "id_sifat"          => $surat_masuk->id_sifat,
            "sifat"             => $surat_masuk->sifatSurat->nama,
            "no_agenda"         => $surat_masuk->no_agenda,
            "file_surat"        => $surat_masuk->dokumen->isEmpty() ? null : $surat_masuk->dokumen->map(
                function ($item, $key) {
                    return [
                        "id"        => $item->id,
                        "nama"      => $item->nama,
                        "ukuran"    => $item->ukuran,
                        "tipe"      => $item->tipe,
                        "url" => route("manage.inbox.download", ["id" => $item->id, "name" => $item->alias])
                    ];
                }
            ),
            "file_surat_form" => $surat_masuk->dokumen->map(function ($item, $key) {
                return [
                    "id"    => $item->id,
                    "name"  => $item->nama,
                ];
            }),
            "pembuat"           => $surat_masuk->user->username ?? "Akun Terhapus",
            "dibuat_tanggal" => Carbon::parse($surat_masuk->created_at)->toString(),
        ];
        $this->setData("deleteNotPermanent", PengaturanUmum::getSetting("delete_mail_not_permanent"));
        $this->setTitle("Detail Surat Masuk", true);
        $this->setData("detailData", $data);
        return $this->runInertia("ManageMail/Inbox/Show");
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
    public function update(Request $request, SuratMasuk $surat_masuk)
    {
        $valid_user_created = $request->user()->jabatan->ijin->w_suratmasuk && $request->user()->id == $surat_masuk->id_pembuat;
        $valid_admin = $request->user()->jabatan->ijin->admin;
        if (!$valid_user_created && !$valid_admin) {
            $toast = Toast::error("Gagal", "Anda tidak diperbolehkan melakukan tindakan ini.");
            return $this->redirectInertia(route("manage.inbox.show", ["surat_masuk" => $surat_masuk->id]), $toast);
        }
        $input = $request->input();
        $validator = Validator::make($input, [
            "perihal" => "required|string|max:254",
            "tanggal_surat" => "required|date|before_or_equal:now",
            "id_sifat" => "required|exists:sifatsurat,id|max:500",
            "no_surat" => "required|string|max:100",
            "no_agenda" => "required|string|max:100",
            "asal_surat" => "required|string|max:500",
            "isi_ringkas" => "nullable|string|max:500",
        ]);
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format pada data form yang dimasukkan.");
            return $this->redirectInertia(route("manage.inbox.show", ["surat_masuk" => $surat_masuk->id]), $toast);
        }

        DB::beginTransaction();
        $surat_masuk->perihal       = $input["perihal"];
        $surat_masuk->tanggal_surat = Carbon::parse($input["tanggal_surat"]);
        $surat_masuk->no_surat      = $input["no_surat"];
        $surat_masuk->no_agenda     = $input["no_agenda"];
        $surat_masuk->asal_surat    = $input["asal_surat"];
        $surat_masuk->isi_ringkas   = $input["isi_ringkas"];
        $surat_masuk->id_sifat      = $input["id_sifat"];
        $surat_masuk->save();
        DB::commit();
        if ($surat_masuk->wasChanged()) {
            $toast = Toast::success("Sukses", "Berhasil mengedit surat!");
        } else {
            $toast = Toast::success("Sukses", "Tidak ada yang berubah.");
        }

        return $this->redirectInertia(route("manage.inbox.show", ["surat_masuk" => $surat_masuk->id]), $toast);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(SuratMasuk $surat_masuk)
    {

        // dd($surat_masuk, "test");
        $is_delete_permanent = !PengaturanUmum::getSetting("delete_mail_not_permanent");
        if ($is_delete_permanent) {
            $valid_user_deleted = request()->user()->jabatan->ijin->dp_surat;
            if (!$valid_user_deleted) {
                $toast = Toast::error("Gagal", "Anda tidak diperbolehkan melakukan tindakan ini.");
                return $this->redirectInertia(route("manage.inbox.index"), $toast);
            }
            $surat_masuk->forceDelete();
        } else {
            $valid_user_deleted = request()->user()->jabatan->ijin->d_surat || (request()->user()->jabatan->ijin->d_miliksurat && request()->user()->id == $surat_masuk->id_pembuat);
            if (!$valid_user_deleted) {
                $toast = Toast::error("Gagal", "Anda tidak diperbolehkan melakukan tindakan ini.");
                return $this->redirectInertia(route("manage.inbox.index"), $toast);
            }
            $surat_masuk->delete();
        }
        $toast = Toast::success("Hapus Surat Masuk", "Penghapusan surat masuk berhasil!");
        return $this->redirectInertia(route("manage.inbox.index"), $toast);
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
    public function uploadFile(Request $request, SuratMasuk $surat_masuk)
    {
        $file = $request->file("file");
        if (empty($file)) {
            return response()->json(["success" => false, "message" => "File is null."], Response::HTTP_BAD_REQUEST);
        }
        $valid_user_created = $request->user()->jabatan->ijin->w_suratmasuk && $request->user()->id == $surat_masuk->id_pembuat;
        $valid_admin = $request->user()->jabatan->ijin->w_suratmasuk && $request->user()->jabatan->ijin->admin;
        if (!$valid_user_created && !$valid_admin) {
            return response("failed.validation_user", Response::HTTP_BAD_REQUEST);
        }
        $validator = Validator::make(["files" => $file], [
            "files" => "required|file|max:4096|mimes:png,jpg,jpeg,gif,bmp,pdf"
        ]);
        if ($validator->fails()) {
            return response("failed.validation", Response::HTTP_BAD_REQUEST);
        }

        $id_surat                           = $surat_masuk->id;
        $extension                          = $file->getClientOriginalExtension();
        $file_name                          = Str::random(18) . "." . $extension;
        $file->storeAs("dokumen_sm/$id_surat", $file_name);

        $file_surat_masuk                   = new DokumenSuratMasuk;
        $file_surat_masuk->ukuran           = $file->getSize();
        $file_surat_masuk->alias            = $file_name;
        $file_surat_masuk->tipe             = $extension;
        $file_surat_masuk->id_suratmasuk    = $surat_masuk->id;
        $file_surat_masuk->nama             = $file->getClientOriginalName() ?? "FileTidakBernama.{$extension}";
        $file_surat_masuk->save();

        return response()->json([
            "fileSuratForm" => [
                "name" => $file->getClientOriginalName(),
                "id" => $file_surat_masuk->id
            ], "fileSurat" => [
                "id" => $file_surat_masuk->id,
                "nama" => $file_surat_masuk->nama,
                "ukuran" => $file_surat_masuk->ukuran,
                "tipe" => $file_surat_masuk->tipe,
                "url" => route("manage.inbox.download", ["id" => $file_surat_masuk->id, "name" => $file_surat_masuk->alias])
            ]
        ]);
    }

    public function deleteFile(Request $request, SuratMasuk $surat_masuk)
    {
        $valid_user_created = $request->user()->jabatan->ijin->w_suratmasuk && $request->user()->id == $surat_masuk->id_pembuat;
        $valid_admin = $request->user()->jabatan->ijin->w_suratmasuk && $request->user()->jabatan->ijin->admin;
        if (!$valid_user_created && !$valid_admin) {
            return response("failed.validation_user", Response::HTTP_BAD_REQUEST);
        }
        $id_surat = $surat_masuk->id;
        $path = "dokumen_sm/$id_surat/";
        $id_file = $request->input("id");
        if (empty($id_file)) {
            return response("failed.not_found", Response::HTTP_BAD_REQUEST);
        }
        $dokumen = DokumenSuratMasuk::where("id_suratmasuk", $id_surat)->where("id", $id_file)->first();
        if (empty($dokumen))
            return response("failed.not_found_dokumen", Response::HTTP_BAD_REQUEST);
        $file_name = $dokumen->alias;
        $full_path = $path . $file_name;

        if (Storage::exists($full_path)) {
            Storage::delete($full_path);
        }
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
        $valid_user = $request->user()->jabatan->ijin->w_suratmasuk;
        $valid_admin = $request->user()->jabatan->ijin->w_suratmasuk && $request->user()->jabatan->ijin->admin;
        if (!$valid_user && !$valid_admin) {
            return response("failed.validation_user", Response::HTTP_BAD_REQUEST);
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

    public function download(Request $request, $id, $name)
    {
        // gambar: ["png", "jpg", "jpeg", "gif", "bmp"],
        // pdf
        if (!$request->user()->jabatan->ijin->r_surat && !$request->user()->jabatan->ijin->admin) {
            return $this->throwOrRedirect("Not Permitted to download.", 404);
        }

        $open = $request->query("open", 0);

        $dokumen = DB::table('dokumen_sm')->where("id", $id)->where("alias", $name)->first();
        if (empty($dokumen)) {
            return $this->throwOrRedirect("File is not found.", 404);
        }
        // dd(,Storage::exists("dokumen_sm/$dokumen->id_suratmasuk/$dokumen->alias"));
        if (Storage::exists("dokumen_sm/$dokumen->id_suratmasuk/$dokumen->alias")) {
            if (in_array($dokumen->tipe, ["pdf", "jpg", "png", "jpeg", "gif", "bmp"]) && $open == 1)
                return response()->file(storage_path("app/dokumen_sm/$dokumen->id_suratmasuk/$dokumen->alias"));
            else {
                return response()->download(storage_path("app/dokumen_sm/$dokumen->id_suratmasuk/$dokumen->alias", $dokumen->alias));
            }
        } else {
            return $this->redirectInertia(route("errors.index", ["code" => 404]));
        }
    }
}
