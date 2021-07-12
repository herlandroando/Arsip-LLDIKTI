<?php

namespace App\Http\Controllers;

use App\Exceptions\ForbiddenSendedDataException;
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

    private $sidebar = [
        "dashboard" => [
            "index" => "0", "icon" => "el-icon-odometer",  "label" => "Dashboard", "has_child" => false, "url" => "home"
        ],
        "manage_mail" => [
            "index" => "1", "icon" => "el-icon-message", "label" => "Kelola Surat", "has_child" => true, "childs" => [
                "inbox" => ["index" => "1-1", "label" => "Surat Masuk", "url" => "manage.inbox.index"],
                "send" => ["index" => "1-2", "label" => "Surat Keluar", "url" => "manage.send.index"],
                "disposisi" => ["index" => "1-4", "label" => "Disposisi", "url" => "manage.send.disposisi"],
            ]
        ],
        "report_mail" => [
            "index" => "2", "icon" => "el-icon-document",  "label" => "Laporan Arsip", "has_child" => true, "childs" => [
                "inbox" => ["index" => "2-1", "label" => "Surat Masuk", "url" => "report.inbox.index"],
                "send" => ["index" => "2-2", "label" => "Surat Keluar", "url" => "report.send.index"],
            ]
        ],
        "recycle_bin" => ["index" => "3", "icon" => "el-icon-delete",  "label" => "Tempat Sampah", "has_child" => false, "url" => "recycle.index"],
        "setting" => [
            "index" => "4", "icon" => "el-icon-setting",  "label" => "Pengaturan", "has_child" => true, "childs" => [
                "employee" => ["index" => "4-1", "label" => "Data Pegawai", "url" => "setting.employee.index"],
                "permission" => ["index" => "4-2", "label" => "Perizinan", "url" => "setting.permission.index"],
                "advanced" => ["index" => "4-3", "label" => "Pengaturan Lanjutan", "url" => "setting.advanced.index"],
            ]
        ],
    ];

    private $toast_config = null;
    private $index_active = null;
    private $utility_config = null;
    private $sended_data;
    private $title = "";
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
            throw new Exception("Key was not found on index sidebar that was defined.");
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
                throw new Exception("Redirect toast must be instance of App\Http\Controllers\Toast");
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
            "total" => 0,
            "lastPage" => 0,
            "perPage" => 0,
            "currentPage" => 0,
        ];
        $pagination["total"] = $entity->total();
        $pagination["lastPage"] = $entity->lastPage();
        $pagination["perPage"] = $entity->perPage();
        $pagination["currentPage"] = $entity->currentPage();
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
            $toast = request()->session()->get("_toast");
            $this->toast_config = new Toast($toast["type"], $toast["title"], $toast["message"]);
        }
        if (!empty($this->toast_config)) {
            $this->sended_data->put("_toast", $this->toast_config->toArray());
        }
        if (!empty($this->title)) {
            $this->sended_data->put("_title", $this->title);
        }
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
                    throw new Exception("The '${real_key}' key was not found for added value on searched key.");
                }
            }
        } else {
            if (!$this->sended_data->has($key)) {
                throw new Exception("The '${key}' key was not found for added value on searched key.");
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
    private string $title = "";
    private string $message = "";
    private string $type = "";
    private array $option = [];

    public function __construct($type, $title, $message, $option = [])
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->option = $option;
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
