<?php

namespace App\Http\Controllers\ManageMail;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Toast;
use App\Models\DetailDisposisi;
use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Traits\HasGetFilterList;
use App\Traits\HasManageableTableQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DisposisiController extends Controller
{
    use HasGetFilterList, HasManageableTableQuery;

    protected string $table_name = "disposisi";

    protected array $filter_list_option = [
        "status" => ["table" => "disposisi", "column" => "status", "distinct" => true],
        "asal" => ["table" => "disposisi", "relation" => "pengguna.id_pengirim", "column" => 'pengguna.nama', "only" => ["pengguna.nama", "pengguna.id"], "distinct" => true],
        "tujuan" => ["table" => "jabatan", "column" => 'nama'],
    ];

    protected array $table_option = [
        "status" => ["column" => "status", "label" => "Status"],
        "no_disposisi" => ["column" => "no_disposisi", "label" => "No. Disposisi"],
        "asal" => ["column" => "id_pengirim", "label" => "Asal", "relation" => "pengguna"],
        "tujuan" => ["column" => "id_jabatan", "label" => "Tujuan"],
        "tenggat_waktu" => ["column" => "expired_at", "label" => "Tenggat Waktu"],
        "tanggal_diubah" => ["column" => "updated_at", "label" => "Tanggal Diubah"],
    ];

    protected array $filter_option = [
        "status" => ["type" => "select"],
        "asal" => ["type" => "select", "label_column" => "pengguna.nama"],
        "tujuan" => ["type" => "select", "label_column" => "jabatan.nama", "permitted" => ["r_all_disposisi"]],
        "tenggat_waktu" => ["type" => "date"],
    ];

    protected array $sort_available = [
        "status" => "status",
        "asal" => "nama",
        "tujuan" => "nama",
        "tenggat_waktu" => "expired_at",
        "tanggal_diubah" => "updated_at",
        "no_disposisi" => "no_disposisi"
    ];

    public function __construct()
    {
        parent::__construct();
        $this->setIndexActive("manage_mail.disposisi");
    }

    public function queryIndex($inertia_request = true)
    {
        $search      = request()->query("search");
        $disposisi   = Disposisi::query();
        // dd($disposisi);
        if (!empty($search)) {
            $disposisi->orWhere($this->table_name . ".isi", "like", "%$search%")->orWhere($this->table_name . ".no_disposisi", "like", "%$search%");
            $this->setData("q_search", $search);
        }
        $filter = $this->filterQuery($disposisi);
        if (!$filter && $inertia_request) {
            $toast = Toast::error("Tidak Diijinkan", "Aksi tersebut tidak di ijinkan untuk jabatan anda.");
            return $this->redirectInertia(back()->getTargetUrl(), $toast);
        }
        $sort = $this->sortQuery($disposisi, true);
        if ($inertia_request) {
            $this->setData("q_filter", $filter["list_filter"]);
            $this->setData("q_filter_tag", $filter["list_tag"]);
            // dd($sort);
            if (!empty($sort)) {
                $this->setData("q_sort", $sort);
            }
        }
        return $disposisi;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // $page        = $request->query("page", 1);
        $disposisi = $this->queryIndex();

        // dd("-");
        // $query = str_replace(array('?'), array('\'%s\''), $disposisi->toSql());
        // $query = vsprintf($query, $disposisi->getBindings());
        // dd($query, $this->getSendedData());
        $disposisi = $disposisi->paginate(10);
        // dd($disposisi);
        if ($disposisi->isNotEmpty()) {
            $this->setPaginate($disposisi);
            $data = $disposisi->map(function ($item, $key) {
                // dd($item->pengirim,$item);
                return [
                    "id"                => $item->id,
                    "id_pengirim"       => $item->id_pengirim ?? null,
                    "no_disposisi"      => $item->no_disposisi,
                    "status"            => $item->status,
                    "asal"              => $item->pengirim->nama,
                    "tujuan"            => $item->jabatan->nama,
                    "tenggat_waktu"     => $item->expired_at,
                    "tanggal_ubah"      => $item->updated_at,
                    "isi"               => $item->isi_ringkas,
                ];
            });
            $this->setData("tableData", $data);
            $this->setData("isAvailable", true);
        } else {
            $this->setData("isAvailable", false);
        }

        $this->setTitle("Kelola Disposisi");

        return $this->runInertia("ManageMail/Disposisi/Index");
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
        $disposisi = $this->queryIndex(false);
        $disposisi = $disposisi->paginate(10);

        if ($disposisi->isEmpty()) {
            $result["empty"] = true;
        }
        $result["lastPage"]     = $disposisi->lastPage();
        $result["currentPage"]  = $disposisi->currentPage();

        if ($result["currentPage"] > $result["lastPage"]) {
            $result["limit"] = true;
            $result["empty"] = false;
        }
        $data = $disposisi->map(function ($item, $key) {
            return [
                "id"                => $item->id,
                "id_pengirim"       => $item->id_pengirim ?? null,
                "no_disposisi"      => $item->no_disposisi,
                "status"            => $item->status,
                "asal"              => $item->pengirim->nama,
                "tujuan"            => $item->jabatan->nama,
                "tenggat_waktu"     => $item->expired_at,
                "tanggal_ubah"      => $item->updated_at,
                "isi"               => $item->isi_ringkas,
            ];
        });
        $result["result"] = $data;
        return response()->json($result);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Disposisi $disposisi)
    {
        // dd($surat_masuk->user, $surat_masuk);
        $surat_masuk = SuratMasuk::find($disposisi->id_suratmasuk);
        // dd($surat_masuk,$disposisi, $disposisi->is_suratmasuk);
        // dd(Carbon::parse($disposisi->expired_at)->toString());
        $data = [
            "id"                => $disposisi->id,
            "is_suratmasuk"     => $disposisi->is_suratmasuk ?? null,
            "no_suratmasuk"     => $surat_masuk->no_surat ?? null,
            "id_suratmasuk"     => $disposisi->id_suratmasuk ?? null,
            "id_pengirim"       => $disposisi->id_pengirim ?? null,
            "id_jabatan"        => $disposisi->id_jabatan ?? null,
            "tenggat_waktu"     => Carbon::parse($disposisi->expired_at)->toString(),//$disposisi->expired_at,
            "no_disposisi"      => $disposisi->no_disposisi,
            "isi"               => $disposisi->isi,
            "status"            => $disposisi->status,
            "pengirim"          => $disposisi->pengirim->nama ?? "Akun Terhapus",
            "tujuan"            => $disposisi->jabatan->nama ?? "Tidak Diketahui",
            "tanggal_buat"      => Carbon::parse($disposisi->created_at)->toString(),
            "tanggal_ubah"      => Carbon::parse($disposisi->updated_at)->toString(),
        ];
        $activity_data = $disposisi->detailDisposisi()->orderBy("id")->get()->map(function ($item, $key) {
            if ($item->keterangan === 'Berakhir' && empty($item->id_pembuat)) {
                $nama = "Sistem";
                $jabatan = "";
            } else {
                $nama = $item->pembuat->nama;
                $jabatan = $item->pembuat->jabatan->nama;
            }
            return [
                "nama" => $nama,
                "jabatan" => $jabatan,
                "idPembuat" => $item->id_pembuat,
                "username" => $item->pembuat->username ?? "",
                "keterangan" => $item->keterangan,
                "isStatus" => $item->is_update_status,
                "isMe" => $item->id_pembuat === request()->user()->id ?? false,
                "tanggal_buat" => $item->created_at,
            ];
        });

        $this->setTitle("Detail Disposisi", true);
        $this->setData("detailData", $data);
        $this->setData("activityData", $activity_data);
        return $this->runInertia("ManageMail/Disposisi/Show");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Disposisi $disposisi)
    {
        $valid_user_created = $request->user()->jabatan->ijin->w_disposisi && $request->user()->id == $disposisi->id_pembuat;
        $valid_admin = $request->user()->jabatan->ijin->admin;
        if (!$valid_user_created && !$valid_admin) {
            $toast = Toast::error("Gagal", "Anda tidak diperbolehkan melakukan tindakan ini.");
            return $this->redirectInertia(route("manage.disposisi.show", ["disposisi" => $disposisi->id]), $toast);
        }
        $input = $request->input();
        $validator = Validator::make($input, [
            "is_suratmasuk" => "required|boolean",
            "no_suratmasuk" => "exclude_if:is_suratmasuk,false|exists:suratmasuk,no_surat",
            "tenggat_waktu" => "required|date|after_or_equal:now",
            "no_disposisi" => "required|string|max:255",
            "isi" => "required|string|max:500",
        ]);
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format pada data form yang dimasukkan.");
            return $this->redirectInertia(route("manage.disposisi.show", ["disposisi" => $disposisi->id]), $toast);
        }

        DB::beginTransaction();
        $disposisi->is_suratmasuk = false;
        if (!empty($input["no_suratmasuk"])) {
            $disposisi->is_suratmasuk = true;
            $disposisi->id_suratmasuk = SuratMasuk::whereNoSurat($input["no_suratmasuk"])->first()->id;
        }
        $disposisi->expired_at    = Carbon::parse($input["tenggat_waktu"]);
        $disposisi->no_disposisi  = $input["no_disposisi"];
        $disposisi->isi     = $input["isi"];
        $disposisi->save();
        DB::commit();
        if ($disposisi->wasChanged()) {
            $toast = Toast::success("Sukses", "Berhasil mengedit disposisi!");
        } else {
            $toast = Toast::success("Sukses", "Tidak ada yang berubah.");
        }

        return $this->redirectInertia(route("manage.disposisi.show", ["disposisi" => $disposisi->id]), $toast);
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
        $this->setTitle("Buat Disposisi", true);
        // session(["temp_file"=>null]);
        // dd(session("temp_file"));

        return $this->runInertia("ManageMail/Disposisi/Create");
    }

    /**
     * Store resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $valid_user = $request->user()->jabatan->ijin->w_disposisi;
        $valid_admin = $request->user()->jabatan->ijin->w_disposisi && $request->user()->jabatan->ijin->admin;
        if (!$valid_user && !$valid_admin) {
            $toast = Toast::error("Gagal", "Anda tidak diperbolehkan melakukan tindakan ini.");
            return $this->redirectInertia(route("manage.disposisi.create"), $toast);
        }

        $input = $request->input();
        // dd($input);
        $validator = Validator::make($input, [
            "is_suratmasuk" => "required|boolean",
            "no_suratmasuk" => "exclude_if:is_suratmasuk,false|exists:suratmasuk,no_surat",
            "tenggat_waktu" => "required|date|after_or_equal:now",
            "no_disposisi" => "required|string|max:255",
            "isi" => "required|string|max:500",
            "tujuan" => "required|exists:jabatan,id",
        ]);
        // dd($validator->errors());

        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format pada data form yang dimasukkan.");
            return $this->redirectInertia(route("manage.disposisi.create"), $toast);
        }

        DB::beginTransaction();
        $disposisi = new Disposisi();
        $disposisi->is_suratmasuk = false;
        if (!empty($input["no_suratmasuk"])) {
            $disposisi->is_suratmasuk = true;
            $disposisi->id_suratmasuk = SuratMasuk::whereNoSurat($input["no_suratmasuk"])->first()->id;
        }
        $disposisi->expired_at    = Carbon::parse($input["tenggat_waktu"]);
        $disposisi->no_disposisi  = $input["no_disposisi"];
        $disposisi->id_jabatan    = $input["tujuan"];
        $disposisi->id_pengirim   = $request->user()->id;
        $disposisi->isi     = $input["isi"];
        $disposisi->status  = Disposisi::BELUM_DIPROSES;
        $disposisi->save();
        DB::commit();
        if ($disposisi->wasChanged()) {
            $toast = Toast::success("Sukses", "Berhasil mengedit disposisi!");
        } else {
            $toast = Toast::success("Sukses", "Tidak ada yang berubah.");
        }

        return $this->redirectInertia(route("manage.disposisi.show", ["disposisi" => $disposisi->id]), $toast);
    }

    public function updateStatus(Request $request, Disposisi $disposisi)
    {
        $status = $request->input("status", null);
        $is_valid = false;
        if (!empty($status)) {
            switch ($status) {
                case Disposisi::DITINJAU:
                    $is_valid = in_array($disposisi->status, [Disposisi::SEDANG_DIPROSES, Disposisi::REVISI]);
                    $is_valid = $request->user()->jabatan->id == $disposisi->id_jabatan;
                    break;
                case Disposisi::SEDANG_DIPROSES:
                    $is_valid = $disposisi->status == Disposisi::BELUM_DIPROSES;
                    $is_valid = $request->user()->jabatan->id == $disposisi->id_jabatan;
                    break;
                case Disposisi::REVISI:
                    $is_valid = $disposisi->status == Disposisi::DITINJAU;
                    $is_valid = $request->user()->id == $disposisi->id_pembuat;
                    break;
                case Disposisi::SELESAI:
                    $is_valid = $disposisi->status == Disposisi::DITINJAU;
                    $is_valid = $request->user()->id == $disposisi->id_pembuat;
                    break;
                default:
                    # code...
                    break;
            }
            if ($is_valid) {
                $last_status = $disposisi->status;
                $disposisi->status = $status;
                $disposisi->save();
                $activity = new DetailDisposisi();
                $activity->keterangan = $status;
                $activity->is_update_status = 1;
                $activity->id_disposisi = $disposisi->id;
                $activity->id_pembuat = $request->user()->id;
                $activity->created_at = now();
                $activity->save();
                $toast = Toast::success("Ubah Status Berhasil", "Berhasil mengubah status disposisi dari $last_status menjadi $status.");
                return $this->redirectInertia(route("manage.disposisi.show", ["disposisi" => $disposisi->id]), $toast);
            }
        }
        $toast = Toast::error("Gagal", "Terjadi kesalahan pada perubahan status, Silahkan refresh browser anda.");
        return $this->redirectInertia(route("manage.disposisi.show", ["disposisi" => $disposisi->id]), $toast);
    }

    public function createActivity(Request $request, Disposisi $disposisi)
    {
        if (in_array($disposisi->status, [Disposisi::DITINJAU, Disposisi::SELESAI, Disposisi::BERAKHIR])) {
            $toast = Toast::error("Gagal", "Tidak bisa mengirim pesan pada status yang telah selesai, ditinjau, atau berakhir.");
            return $this->redirectInertia(route("manage.disposisi.show", ["disposisi" => $disposisi->id]), $toast);
        }
        if ($request->user()->id_jabatan != $disposisi->id_jabatan && !$request->user()->id != $disposisi->id_pembuat) {
            $toast = Toast::error("Gagal", "Tidak bisa mengirim pesan pada status. Anda tidak termasuk dalam aktifitas disposisi ini.");
            return $this->redirectInertia(route("manage.disposisi.show", ["disposisi" => $disposisi->id]), $toast);
        }
        $input = $request->input();
        $validator = Validator::make($input, [
            "keterangan" => "required|string|min:2|max:500",
        ]);
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format pada data form yang dimasukkan.");
            return $this->redirectInertia(route("manage.disposisi.show", ["disposisi" => $disposisi->id]), $toast);
        }
        $activity = new DetailDisposisi;
        $activity->keterangan = $input["keterangan"];
        $activity->id_disposisi = $disposisi->id;
        $activity->id_pembuat = $request->user()->id;
        $activity->is_update_status = 0;
        $activity->created_at = now();
        $activity->save();

        $toast = Toast::success("Pesan Terkirim", "Keterangan disposisi anda telah terkirim.");
        return $this->redirectInertia(route("manage.disposisi.show", ["disposisi" => $disposisi->id]), $toast);
    }

    public function getListSuratMasuk()
    {
        $no_surat = request()->query("no_surat");
        if (!empty($no_surat)) {
            $surat_masuk = SuratMasuk::whereNoSurat($no_surat);
        } else {
            $surat_masuk = SuratMasuk::query();
        }
        $surat_masuk = $surat_masuk->select(["id", "no_surat", "perihal"])->limit(10)->get();
        if ($surat_masuk->isNotEmpty()) {
            return response()->json($surat_masuk);
        } else {
            return response()->json([]);
        }
    }

    public function destroy(Disposisi $disposisi)
    {
        $valid_user_deleted = request()->user()->jabatan->ijin->d_surat || (request()->user()->jabatan->ijin->d_miliksurat && request()->user()->id == $disposisi->id_pembuat);
        if (!$valid_user_deleted) {
            $toast = Toast::error("Gagal", "Anda tidak diperbolehkan melakukan tindakan ini.");
            return $this->redirectInertia(route("manage.disposisi.index"), $toast);
        }
        // dd($surat_keluar, "test");
        $disposisi->delete();
        $toast = Toast::success("Hapus Disposisi", "Penghapusan disposisi berhasil!");
        return $this->redirectInertia(route("manage.disposisi.index"), $toast);
    }
}
