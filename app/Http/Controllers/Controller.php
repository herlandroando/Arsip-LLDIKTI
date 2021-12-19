<?php

namespace App\Http\Controllers;

use App\Exceptions\ForbiddenSendedDataException;
use App\Exceptions\RenderException;
use App\Models\Jabatan;
use App\Models\SifatSurat;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Main controller laravel that was modified to handle inertia request
 *
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $sidebar = [
        "dashboard" => [
            "index" => "0", "icon" => "el-icon-odometer", "permission" => [],  "label" => "Dashboard", "has_child" => false, "url" => "home"
        ],
        "manage_mail" => [
            "index" => "1", "icon" => "el-icon-message", "permission" => ["r_surat"], "label" => "Kelola Surat", "has_child" => true, "childs" => [
                "inbox" => ["index" => "1-1", "label" => "Surat Masuk", "url" => "manage.inbox.index"],
                "send" => ["index" => "1-2", "label" => "Surat Keluar", "url" => "manage.send.index"],
                "disposisi" => ["index" => "1-4", "label" => "Disposisi", "url" => "manage.disposisi.index"],
            ]
        ],
        "report_mail" => [
            "index" => "2", "icon" => "el-icon-document", "permission" => ["r_laporan"],  "label" => "Laporan Arsip", "has_child" => false, "url" => "report.index"
        ],
        "recycle" => ["index" => "3", "icon" => "el-icon-delete", "permission" => ["dp_surat"],  "label" => "Tempat Sampah", "has_child" => true, "childs" => [
            "inbox" => ["index" => "3-1", "label" => "Surat Masuk", "url" => "recycle.inbox.index"],
            "send" => ["index" => "3-2", "label" => "Surat Keluar", "url" => "recycle.send.index"],
        ]],
        "setting" => [
            "index" => "4", "icon" => "el-icon-setting", "permission" => ["admin"], "label" => "Pengaturan", "has_child" => true, "childs" => [
                "users" => ["index" => "4-1", "label" => "Kelola Pengguna", "url" => "setting.users.index"],
                "permission" => ["index" => "4-2", "label" => "Kelola Hak Akses", "url" => "setting.permission.index"],
                "jabatan" => ["index" => "4-3", "label" => "Kelola Jabatan", "url" => "setting.jabatan.index"],
                "advance" => ["index" => "4-4", "label" => "Pengaturan Lanjutan", "url" => "setting.advance.index"],
            ]
        ],
        // "manual" => [
        //     "index" => "5", "icon" => "el-icon-info-filled", "permission" => [], "label" => "Bantuan", "has_child" => true, "childs" => [
        //         "program" => ["index" => "4-1", "label" => "Manual Program", "url" => ""],
        //         "about" => ["index" => "4-2", "label" => "Tentang Aplikasi", "url" => "setting.permission.index"],
        //     ]
        // ],
    ];

    private $limitPagination = 10;
    private $toast_config = null;
    private $index_active = null;
    private $utility_config = null;
    private $sended_data;
    private $title = "";
    private $show_title = true;
    private $back_option = [];
    // private $breadcrumb = "";

    /**
     * Enable pagination data. Set config from `setPaginate()`
     */
    public bool $pagination = false;

    public function __construct()
    {
        $this->sended_data = collect([
            "_toast" => null,
            "_utility" => null,
        ]);
    }

    /**
     * Set sidebar's index activated by key. It can be deep by example `manage.inbox` reference by `$sidebar`
     *
     * @param string $key
     * @return void
     */
    public function setIndexActive($key)
    {
        $key        = explode(".", $key);
        $parent     = $this->sidebar[$key[0]] ?? null;
        $is_null    = false;
        $index      = "";
        if (empty($parent)) {
            $is_null = true;
        }
        if (!$is_null && $parent["has_child"]) {
            $child = $parent["childs"][$key[1]] ?? null;
            if (empty($child)) {
                $is_null    = true;
            } else {
                $index      = $child["index"];
            }
        } else {
            $index = $parent["index"];
        }
        if ($is_null) {
            $this->throwOrRedirect("Key was not found on index sidebar that was defined.", 500);
        }
        $this->index_active = $index;
        // dd($index);
    }

    public function setToast(Toast $toast)
    {
        $this->toast_config = $toast;
    }

    /**
     * Set title of page and config if page have back navigation.
     *
     * @param string $title
     * Set title page. It will out at topbar too.
     * @param bool $has_back
     * Set page have back navigation on topbar
     * @param string $url_back
     * Set url redirect for back at page. If null, it will back depend from history browser client.
     * @return void
     */
    public function setTitle(string $title, bool $has_back = false, string $url_back = null)
    {
        $this->title = $title;
        $this->back_option["has_back"] = $has_back;
        $this->back_option["url_back"] = $url_back;
    }

    public function showTitle(bool $state)
    {
        $this->show_title = $state;
    }

    // /**
    //  * Set breadcrumb of page.
    //  *
    //  * @param string|array $breadcrumb
    //  * @return void
    //  */
    // public function setBreadcrumb($breadcrumb)
    // {
    //     if (is_array($breadcrumb)){
    //         $this->breadcrumb = implode("/",$breadcrumb);
    //     }
    //     else{
    //         $this->breadcrumb =
    //     }
    // }

    /**
     * Set data and replaced key and value that was added before if the key is same.
     *
     * @param array|string $key
     * @param mixed $value
     * @return void
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $real_key => $val) {
                $this->validateKey($real_key);
            }
            $this->sended_data->mergeRecursive($key);
        } else {
            $this->validateKey($key);
            $this->sended_data->put($key, $value);
        }
    }

    public function getSendedData()
    {
        return $this->sended_data;
    }

    public function addData(string $key, $value)
    {
        $this->validateKey($key);
        $this->keyExist($key);
        $this->sended_data->put($key, $value);
    }

    /**
     * Redirect with inertia default data set from this server.
     *
     * @param string $url
     * Url that want to redirect user.
     * @param Toast $toast
     * Set toast to next session with flashed and automatically set the toast setting so it will sended with `runInertia()`
     * @param array $other_data
     * Other data that want to be on next session with flashed.
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function redirectInertia($url, $toast = null, $other_data = [])
    {
        $redirect =  redirect($url);
        if (!empty($toast)) {
            if (!($toast instanceof Toast)) {
                $this->throwOrRedirect("Redirect toast must be instance of App\Http\Controllers\Toast", 500);
            }
            $redirect = $redirect->with(["_toast" => $toast->toArray()]);
        }
        if (!empty($other_data))
            $redirect = $redirect->with($other_data);
        return $redirect;
    }

    /**
     * Set data of pagination to inertia frontend and automatically enable pagination.
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $entity
     * It will automatically assign to data without config.
     * @return void
     */
    public function setPaginate(LengthAwarePaginator $entity)
    {
        $this->pagination = true;
        $pagination = [
            "total"         => 0,
            "limit"         => $this->limitPagination,
            "lastPage"      => 0,
            "perPage"       => 0,
            "currentPage"   => 0,
        ];
        $pagination["total"]        = $entity->total();
        $pagination["lastPage"]     = $entity->lastPage() > 10 ? 10 : $entity->lastPage();
        $pagination["perPage"]      = $entity->perPage();
        $pagination["currentPage"]  = $entity->currentPage();
        $pagination = $this->sended_data->put("_pagination", $pagination);
    }

    /**
     * Run inertia and sended all data that was set on. If data has key named tableData it will automatically add
     * pagination data at inertia frontend.
     *
     * @param string $component
     * @return void
     */
    public function runInertia(string $component)
    {
        // dd($this);
        if (Toast::isAvailable()) {
            $toast              = request()->session()->get("_toast");
            $this->toast_config = new Toast($toast["type"], $toast["title"], $toast["message"]);
        }
        if (!empty($this->toast_config)) {
            $this->sended_data->put("_toast", $this->toast_config->toArray());
        }
        if (!empty($this->title)) {
            $this->sended_data->put("_title", $this->title);
        }
        $this->sended_data->put("_showTitle", $this->show_title);
        if (Arr::has($this->back_option, "has_back") && $this->back_option["has_back"]) {
            $this->sended_data->put("_backNav", ["hasBack" => true, "urlBack" => $this->back_option["url_back"] ?? ""]);
        }
        $this->sended_data->put("_index", $this->index_active);
        // dd($this->sended_data,$this,!empty($this->toast_config));
        return inertia($component, $this->sended_data)
            ->withViewData("_sidebars", $this->sidebar)
            ->withViewData("_sifat_surat", SifatSurat::all())
            ->withViewData("_bagianInstansi", Jabatan::all()->makeHidden("id_ijin"));
    }

    /**
     * Error handler for throw if debug and redirect to error page if production.
     *
     * @return void
     */
    public function throwOrRedirect($message, $code)
    {
        if (env("APP_DEBUG", true)) {
            throw new Exception($message);
        } else {
            return redirect(route("errors.index", ["code" => $code]));
        }
    }

    private function validateKey($key)
    {
        if (substr($key, 0, 1) === "_") {
            throw new ForbiddenSendedDataException("The keys you enter or set here are reserved keys.");
        }
    }
    private function keyExist($key)
    {
        if (strpos($key, ".") !== FALSE) {
            $key = explode(".", $key);
            $check_key = "";
            foreach ($key as $real_key) {
                if (empty($check_key)) {
                    $check_key .= $real_key;
                } else {
                    $check_key .= ".{$real_key}";
                }
                if (!$this->sended_data->has($check_key)) {
                    $this->throwOrRedirect("The '${real_key}' key was not found for added value on searched key.", 500);
                }
            }
        } else {
            if (!$this->sended_data->has($key)) {
                $this->throwOrRedirect("The '${key}' key was not found for added value on searched key.", 500);
            }
        }
    }
}

//----------------------------------------------- TOAST -------------------------------------------------

/**
 * Toast service handler. You can use static function to automatically add to frontend with `runInertia()` function.
 */
class Toast
{
    const SUCCESS = "success";
    const ERROR = "error";
    const WARN = "warning";
    const INFO = "info";
    private string $title   = "";
    private string $message = "";
    private string $type    = "";
    private array $option   = [];

    public function __construct($type, $title, $message, $option = [])
    {
        $this->title    = $title;
        $this->message  = $message;
        $this->type     = $type;
        $this->option   = $option;
    }

    /**
     * Check if toast has been flashed from previous session.
     *
     * @return boolean
     */
    public static function isAvailable()
    {
        return request()->session()->has("_toast");
    }

    public static function set($type, $title, $message, $option = [])
    {
        $toast = new self($type, $title, $message, $option);
        request()->route()->controller->setToast($toast);
        return $toast;
    }

    public static function success($title, $message, $option = [])
    {
        return self::set(self::SUCCESS, $title, $message, $option);
    }

    public static function error($title, $message, $option = [])
    {
        return self::set(self::ERROR, $title, $message, $option);
    }

    public static function warn($title, $message, $option = [])
    {
        return self::set(self::WARN, $title, $message, $option);
    }

    public static function info($title, $message, $option = [])
    {
        return self::set(self::INFO, $title, $message, $option);
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'type' => $this->type,
            'message' => $this->message,
            'option' => $this->option,
        ];
    }

    // public function __unserialize(array $data)
    // {
    //     $this->title = $data['title'];
    //     $this->message = $data['message'];
    //     $this->option = $data['option'];
    // }

}
