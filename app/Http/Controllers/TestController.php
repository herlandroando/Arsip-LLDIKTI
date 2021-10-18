<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    //

    public function index()
    {
        $this->reportPdf("sm_report","monthly");
    }

    private function reportPdf($type, $routine)
    {
        $type_surat = ["sm_report" => "suratmasuk", "sk_report" => "suratkeluar"];
        $type_title = ["sm_report" => "Laporan Surat Masuk", "sk_report" => "Laporan Surat Keluar"];
        $routine_title = ["daily" => "Harian", "monthly" => "Bulanan"];
        $data = [];
        try {
            //code...

            if ($routine == "monthly") {
                view()->share(["report_metadata" => [
                    "duration" => Carbon::now()->subDay()->startOfMonth()->toDayDateTimeString() . " - " . Carbon::now()->subDay()->endOfMonth()->toDayDateTimeString(),
                    "type" => $type_title[$type],
                    "routine_type" => $routine_title[$routine],
                ]]);
                $date_collected = DB::table($type_surat[$type])->selectRaw("date(local_created_at) as tanggal_buat")
                    ->groupBy("tanggal_buat")->orderBy('tanggal_buat', 'asc')->get()->pluck("tanggal_buat");
                if ($date_collected->isEmpty()) {
                    view()->share("report_data", []);
                } else {
                    foreach ($date_collected as $key => $value) {
                        $start = Carbon::parse($value)->startOfDay();
                        $end = Carbon::parse($value)->endOfDay();
                        array_push($data,[
                            "duration_date" => $start->toDayDateTimeString() . " - " . $end->toDayDateTimeString(),
                            "data" => $type == "sm_report" ? $this->suratMasukQuery([$start, $end]) : $this->suratKeluarQuery([$start, $end]),
                        ]);
                    }
                    dd($data);
                    view()->share("report_data", $data);
                }
            } else {
                view()->share(["report_metadata" => [
                    "duration" => Carbon::now()->subDay()->startOfDay()->toDayDateTimeString() . " - " . Carbon::now()->subDay()->endOfDay()->toDayDateTimeString(),
                    "type" => $type_title[$type],
                    "routine_type" => $routine_title[$routine],
                ]]);
                $start = Carbon::now()->subDay()->startOfDay();
                $end = Carbon::now()->subDay()->endOfDay();
                $data = $type == "sm_report" ? $this->suratMasukQuery([$start, $end]) : $this->suratKeluarQuery([$start, $end]);
                view()->share("report_data", $data);
            }

            view()->share(["title" => $type_title[$type]]);

            view()->share(["report_type" => $type]);
            view()->share(["routine_type" => $routine]);
            view()->share(["report_created_at" => Carbon::now()->timezone("Asia/Jayapura")->toDayDateTimeString()]);
            $filename = "$type-$routine-" . Carbon::now()->timestamp . ".pdf";
            $pdf = PDF::loadView("reports.main")->setPaper('a4', 'landscape')->save(storage_path("app/reports/$filename"));
        } catch (\Throwable $th) {
            ob_start();
            var_dump($start, $end, $data, $routine, $type);
            $content = ob_get_contents();
            ob_end_clean();
            Storage::disk('local')->put("error-$routine-$type.txt", $content);
            Storage::disk('local')->put("error-$routine-$type-log.txt", $th);
        }
        // ob_start();
        // var_dump($start, $end, $data, $routine, $type);
        // $content = ob_get_contents();
        // ob_end_clean();
        // Storage::disk('local')->put("dump-$routine-$type.txt", $content);
        DB::table('laporan')->insert(["type" => $type, "routine_type" => $routine, "created_at" => Carbon::now(), "path" => $filename]);
    }

    private function suratKeluarQuery($between)
    {
        $surat_keluar = SuratKeluar::whereBetween("local_created_at", [$between[0], $between[1]])
            // /*->where("created_at", ">=", $start)*/->where("created_at", ">=", $end)
            ->whereNull("deleted_at")->orderBy('local_created_at', 'asc')->get();
        // dd($surat_masuk->toSql(), $surat_masuk->getBindings(), $start, $end,$surat_masuk->get());
        if ($surat_keluar->isNotEmpty()) {
            return $surat_keluar->map(function ($surat) {
                return [
                    "no_surat" => $surat->no_surat,
                    "tanggal_surat" => $surat->tanggal_surat,
                    "perihal" => $surat->perihal,
                    "nama_bagian" => $surat->jabatan->nama,
                    "nama_pembuat" => $surat->custom_nama_pembuat
                ];
            });
            // $surat_masuk->map(function($value){
            //     $value->no_agenda
            // });
        } else {
            return [];
        }
    }
    private function suratMasukQuery($between)
    {
        $surat_masuk = DB::table('suratmasuk')->select(["id", "no_agenda", "no_surat", "created_at", "tanggal_surat", "asal_surat", "perihal"])
            ->whereBetween("local_created_at", [$between[0], $between[1]])->orderBy('local_created_at', 'asc')
            ->whereNull("deleted_at")->get();

        if ($surat_masuk->isNotEmpty()) {
            return $surat_masuk;
        } else {
            return [];
        }
    }
}
