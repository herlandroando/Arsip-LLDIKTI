<?php

namespace App\Http\Controllers;

use App\Traits\HasGetFilterList;
use App\Traits\HasManageableTableQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    protected string $table_name = "laporan";

    protected array $table_option = [
        "tipe" => ["column" => "type", "label" => "Tipe"],
        "tanggal_buat" => ["column" => "created_at", "label" => "Tanggal Buat",],
        "rutinitas" => ["column" => "routine_type", "label" => "Rutinitas"],
    ];

    protected array $filter_option = [
        "tipe" => ["type" => "select", "custom_label" => [
            'sm_surat' => 'Laporan Surat Masuk',
            'sk_surat' => 'Laporan Surat Keluar'
        ]],
        "rutinitas" => ["type" => "select", "custom_label" => ["daily" => "Harian", "monthly" => "Bulanan"]],
        "tanggal_buat" => ["type" => "date"],
    ];

    protected array $sort_available = [
        "tipe" => "type", "rutinitas" => "routine_type", "tanggal_buat" => "created_at"
    ];

    use HasManageableTableQuery;

    public function queryIndex($inertia_request = true)
    {
        $search         = request()->query("search");
        $laporan    = DB::table("laporan");
        // dd($surat_masuk);
        // if (!empty($search)) {
        // $surat_masuk->orWhere("asal_surat", "like", "%$search%")->orWhere($this->table_name . ".no_surat", "like", "%$search%")->orWhere($this->table_name . ".perihal", "like", "%$search%");
        // $this->setData("q_search", $search);
        // }
        $filter = $this->filterQuery($laporan);
        $sort = $this->sortQuery($laporan, true);
        if ($inertia_request) {
            $this->setData("q_filter", $filter["list_filter"]);
            $this->setData("q_filter_tag", $filter["list_tag"]);
            // dd($sort);
            if (!empty($sort)) {
                $this->setData("q_sort", $sort);
            }
        }
        return $laporan;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // $page        = $request->query("page", 1);
        $laporan = $this->queryIndex();

        $laporan = $laporan->paginate(10);
        // dd($surat_masuk);
        if ($laporan->isNotEmpty()) {
            $this->setPaginate($laporan);
            $data = $laporan->map(function ($item, $key) {
                return [
                    "id"          => $item->id,
                    "tipe"        => $item->type == "sm_report" ? "Laporan Surat Masuk" : "Laporan Surat Keluar",
                    "rutinitas"   => $item->routine_type == "daily" ? "Daily (Harian)" : "Monthly (Bulanan)",
                    "namaFile"    => $item->path,
                    "tanggal_buat" => $item->created_at,
                    "linkFile"    => route("report.download", ["id" => $item->id, "name" => $item->path]),
                ];
            });
            $this->setData("tableData", $data);
            $this->setData("isAvailable", true);
        } else {
            $this->setData("isAvailable", false);
        }

        $this->setTitle("Laporan");

        return $this->runInertia("Report/Index");
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
        $laporan = $this->queryIndex(false);
        $laporan = $laporan->paginate(10);

        if ($laporan->isEmpty()) {
            $result["empty"] = true;
        }
        $result["lastPage"]     = $laporan->lastPage();
        $result["currentPage"]  = $laporan->currentPage();

        if ($result["currentPage"] > $result["lastPage"]) {
            $result["limit"] = true;
            $result["empty"] = false;
        }
        $data = $laporan->map(function ($item, $key) {
            return [
                "id"          => $item->id,
                "tipe"        => $item->type == "sm_report" ? "Laporan Surat Masuk" : "Laporan Surat Keluar",
                "rutinitas"   => $item->routine_type == "daily" ? "Daily (Harian)" : "Monthly (Bulanan)",
                    "tanggal_buat" => $item->created_at,
                    "namaFile"    => $item->path,
                "linkFile"    => route("report.download", ["id" => $item->id, "name" => $item->path]),
            ];
        });
        $result["result"] = $data;
        return response()->json($result);
    }

    public function download($id, $name)
    {
        $laporan = DB::table('laporan')->where("id", $id)->where("path", $name)->first();
        if (empty($laporan)) {
            return $this->throwOrRedirect("Report is not found.", 404);
        }
        // dd($laporan,Storage::exists("reports/$laporan->path"));
        if (Storage::exists("reports/$laporan->path"))
            return response()->file(storage_path("app/reports/$laporan->path"));
        else {
            return $this->redirectInertia(route("errors.index",["code"=>404]));
        }
    }
}
