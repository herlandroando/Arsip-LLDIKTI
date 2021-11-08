<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Toast;
use Closure;
use Exception;
use Illuminate\Http\Request;

class PermissionFunction
{
    private $permission = [
        "disposisi" => [
            "w" => ["r_surat", "w_disposisi"],
            "d" => ["r_surat", "w_disposisi", ["d_surat", "d_miliksurat"]],
            "r" => ["r_surat"],
            "ra" => ["r_surat", "r_all_disposisi"],
        ],
        "inbox" => [
            "w" => ["r_surat", "w_suratmasuk"],
            "d" => ["r_surat", "w_suratmasuk", ["d_surat", "d_miliksurat"]],
            "r" => ["r_surat"],
        ],
        "send" => [
            "w" => ["r_surat", "w_suratkeluar"],
            "d" => ["r_surat", "w_suratkeluar", ["d_surat", "d_miliksurat"]],
            "r" => ["r_surat"],
        ],
        "setting" => [
            "a" => ["admin"],
        ],
        "report" => [
            "r" => ["r_laporan"],
        ],
        "recycle" => [
            "r" => ["dp_surat"]
        ],
        "admin" => [
            "r" => ["admin"],
        ]
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $name)
    {
        if (empty($request->user())) {
            return redirect(route("login"))->with("_toast", Toast::info("Sesi Habis", "Sesi anda telah habis. Silahkan login terlebih dahulu.")->toArray());
        } elseif (empty($request->user()->jabatan) || empty($request->user()->jabatan->ijin)) {
            return $this->throwOrRedirect("Not login.", 404);
        }

        try {
            list($path, $code) = explode(".", $name);
            if (empty($path) || empty($code)) {
                return $this->throwOrRedirect("Path/code of permission bad formatted 1", 500);
            }
        } catch (\Throwable $th) {
            return $this->throwOrRedirect("Path/code of permission bad formatted 2", 500);
        }
        $path_keys = array_keys($this->permission);
        if (!in_array($path, $path_keys)) {
            return $this->throwOrRedirect("Path/code of permission bad formatted 3", 500);
        }
        $code_keys = array_keys($this->permission[$path]);
        // dd($code_keys, $code);
        if (!in_array($code, $code_keys)) {
            return $this->throwOrRedirect("Path/code of permission bad formatted 4", 500);
        }

        // dd($request->user()->jabatan->ijin()->hasPermission($this->permission[$path][$code]), $this->permission[$path][$code]);
        if (!$request->user()->jabatan->ijin()->hasPermission($this->permission[$path][$code])) {
            return $this->throwOrRedirect("Not Permitted.", 404);
        }
        // dd("ssc");

        return $next($request);
    }

    public function throwOrRedirect($message, $code)
    {
        // dump("ssd");
        if (env("APP_DEBUG", true)) {
            throw new Exception($message);
        } else {
            // dd("ssc", $message);
            return redirect(route("errors.index", ["code" => $code]));
        }
    }
}
