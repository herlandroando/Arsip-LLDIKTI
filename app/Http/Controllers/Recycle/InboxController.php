<?php

namespace App\Http\Controllers\Recycle;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Toast;
use App\Models\SuratMasuk;
use App\Traits\HasGetFilterList;
use App\Traits\HasManageableTableQuery;
use Illuminate\Http\Request;

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
        $this->setIndexActive("recycle.inbox");
    }

    public function queryIndex($inertia_request = true)
    {
        $search         = request()->query("search");
        $surat_masuk    = SuratMasuk::query()->onlyTrashed();
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

        $this->setTitle("Tempat Sampah - Surat Masuk");

        return $this->runInertia("Recycle/Inbox/Index");
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

    public function destroy(SuratMasuk $surat_masuk)
    {
        // dd($surat_masuk, "test");
        $surat_masuk->forceDelete();
        $toast = Toast::success("Hapus Permanen Berhasil", "Penghapusan surat masuk berhasil!");
        return $this->redirectInertia(route("recycle.inbox.index"), $toast);
    }

    public function restore($surat_masuk)
    {
        $surat_masuk = SuratMasuk::withTrashed()->find($surat_masuk);
        $surat_masuk->restore();
        $toast = Toast::success("Kembalikan Berhasil", "Pengembalian surat masuk berhasil!");
        return $this->redirectInertia(route("recycle.inbox.index"), $toast);
    }
}
