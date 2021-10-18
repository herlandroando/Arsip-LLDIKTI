<?php

namespace App\Http\Controllers\Recycle;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Toast;
use App\Models\SuratKeluar;
use App\Traits\HasGetFilterList;
use App\Traits\HasManageableTableQuery;
use Illuminate\Http\Request;

class SendController extends Controller
{
    use HasGetFilterList, HasManageableTableQuery;

    protected string $table_name = "suratkeluar";

    protected array $filter_list_option = [
        "sifat" => ["table" => "sifatsurat", "only" => ["nama", "id"]],
        "pembuat" => ["table" => "suratkeluar", "column" => 'nama_pembuat', "distinct" => true],
        "asal_surat" => ["table" => "suratkeluar","distinct"=>true, "column" => 'nama', "relation" => "jabatan.asal_surat", "only" => ["jabatan.nama", "jabatan.id"]],
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
        $search         = request()->query("search");
        $surat_keluar   = SuratKeluar::query()->onlyTrashed();
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
                    "no_surat"      => $item->no_surat,
                    "sifat"         => $item->sifatSurat->nama,
                    "asal_surat"    => $item->jabatan->nama,
                    "perihal"       => $item->perihal,
                    "tanggal_surat" => $item->tanggal_surat,
                    "pembuat"       => $item->nama_pembuat,
                ];
            });
            $this->setData("tableData", $data);
            $this->setData("isAvailable", true);
        } else {
            $this->setData("isAvailable", false);
        }

        $this->setTitle("Tempat Sampah - Surat Keluar");

        return $this->runInertia("Recycle/Send/Index");
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
                "no_surat"      => $item->no_surat,
                "sifat"         => $item->sifatSurat->nama,
                "asal_surat"    => $item->jabatan->nama,
                "perihal"       => $item->perihal,
                "tanggal_surat" => $item->tanggal_surat,
                "pembuat"       => $item->nama_pembuat,
            ];
        });
        $result["result"] = $data;
        return response()->json($result);
    }

    public function destroy(SuratKeluar $surat_keluar)
    {
        // dd($surat_masuk, "test");
        $surat_keluar->forceDelete();
        $toast = Toast::success("Hapus Permanen Berhasil", "Penghapusan surat masuk berhasil!");
        return $this->redirectInertia(route("recycle.inbox.index"), $toast);
    }

    public function restore(SuratKeluar $surat_keluar){
        $surat_keluar->restore();
        $toast = Toast::success("Kembalikan Berhasil", "Pengembalian surat masuk berhasil!");
        return $this->redirectInertia(route("recycle.inbox.index"), $toast);
    }
}
