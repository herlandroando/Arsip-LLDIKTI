<?php

namespace App\Http\Controllers\ManageMail;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Toast;
use App\Http\Requests\StoreSuratMasukRequest;
use App\Models\DokumenSuratMasuk;
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
        $search         = request()->query("search");
        $surat_masuk    = SuratMasuk::query();
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
                    "no_surat"      => $item->no_surat,
                    "sifat"         => $item->sifatSurat->nama,
                    "asal_surat"    => $item->asal_surat,
                    "perihal"       => $item->perihal,
                    "tanggal_surat" => $item->tanggal_surat,
                    "no_agenda"     => $item->no_agenda,
                ];
            });
            $this->setData("tableData", $data);
            $this->setData("isAvailable", true);
        } else {
            $this->setData("isAvailable", false);
        }

        $this->setTitle("Surat Masuk");

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
                "no_surat"      => $item->no_surat,
                "sifat"         => $item->sifatSurat->nama,
                "asal_surat"    => $item->asal_surat,
                "perihal"       => $item->perihal,
                "tanggal_surat" => $item->tanggal_surat,
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
        $input = $request->input();
        $validator = Validator::make($input, [
            "perihal" => "required|string|max:254",
            "tanggal_surat" => "required|date|before_or_equal:now",
            "id_sifat" => "required|exists:sifatsurat,id|max:500",
            "no_surat" => "required|string|max:100",
            "no_agenda" => "required|string|max:100",
            "asal_surat" => "required|string|max:500",
            "isi_ringkas" => "required|string|max:500",
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
                $path_file                          = "images/temp/{$file['id']}";
                $target_path                        = "images/dokumen_sm/{$surat_masuk->id}/";
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

        return $this->redirectInertia(route("manage.inbox.create"), $toast);
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
        // dd($surat_masuk->user, $surat_masuk);
        $data = [
            "id"                => $surat_masuk->id,
            "perihal"           => $surat_masuk->perihal,
            "tanggal_surat"     => $surat_masuk->tanggal_surat,
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
                        "url"       => public_path("")
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
            "dibuat_tanggal"    => $surat_masuk->created_at,
        ];

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
        $input = $request->input();
        $validator = Validator::make($input, [
            "perihal" => "required|string|max:254",
            "tanggal_surat" => "required|date|before_or_equal:now",
            "id_sifat" => "required|exists:sifatsurat,id|max:500",
            "no_surat" => "required|string|max:100",
            "no_agenda" => "required|string|max:100",
            "asal_surat" => "required|string|max:500",
            "isi_ringkas" => "required|string|max:500",
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
        $surat_masuk->delete();
        $toast = Toast::success("Hapus Surat Masuk", "Penghapusan surat masuk berhasil!");
        return $this->redirectInertia(route("manage.inbox.index"), $toast);
    }

    public function deleteTemp(Request $request)
    {
        $session_temp_file = session("temp_file");
        $id = $request->input("id");
        if (empty($session_temp_file) || empty($id)) {
            return response("failed", Response::HTTP_BAD_REQUEST);
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
        $id_surat                           = $surat_masuk->id;
        $extension                          = $file->getClientOriginalExtension();
        $file_name                          = Str::random(18) . "." . $extension;
        $file->storeAs("images/dokumen_sm/$id_surat", $file_name);

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
                "url" => public_path("")
            ]
        ]);
    }

    public function deleteFile(Request $request, SuratMasuk $surat_masuk)
    {
        $id_surat = $surat_masuk->id;
        $path = "images/dokumen_sm/$id_surat/";
        $id_file = $request->input("id");
        if (empty($id_file)) {
            return response("failed", Response::HTTP_BAD_REQUEST);
        }
        $dokumen = DokumenSuratMasuk::where("id_suratmasuk", $id_surat)->where("id", $id_file)->first();
        if (empty($dokumen))
            return response("failed", Response::HTTP_BAD_REQUEST);
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
        $file = $request->file("file");
        $date = now()->startOfDay()->timestamp;
        $file_name = Str::random(4) . (string)$date . ".{$file->extension()}";
        $store_session_name =  "$date/$file_name";
        $file->storeAs("images/temp/$date", $file_name);
        $temp_file = session("temp_file") ?? [];
        array_push($temp_file, ["name" => $file->getClientOriginalName(), "id" => $store_session_name]);
        session(["temp_file" => $temp_file]);

        return response()->json(["name" => $file->getClientOriginalName(), "id" => $store_session_name]);
    }
}
