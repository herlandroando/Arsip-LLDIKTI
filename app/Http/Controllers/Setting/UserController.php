<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Toast;
use App\Models\Jabatan;
use App\Models\User;
use App\Traits\HasManageableTableQuery;
use App\Traits\HasGetFilterList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use HasGetFilterList, HasManageableTableQuery;

    protected string $table_name = "pengguna";

    protected array $filter_list_option = [
        "jabatan" => ["table" => "jabatan", "only" => ["nama", "id"]],
    ];

    protected array $table_option = [
        "nama" => ["column" => "nama", "label" => "Nama"],
        "nip" => ["column" => "nip", "label" => "NIP"],
        "jabatan" => ["column" => "id_jabatan", "label" => "Jabatan", "relation" => "jabatan"],
    ];

    protected array $filter_option = [
        "jabatan" => ["type" => "select", "label_column" => "jabatan.nama"],
    ];

    protected array $sort_available = [
        "nama" => "nama", "nip" => "nip", "jabatan" => "nama"
    ];

    public function __construct()
    {
        parent::__construct();
        $this->setIndexActive("setting.users");
    }

    public function queryIndex($inertia_request = true)
    {
        $search         = request()->query("search");
        $user           = User::query();
        // dd($user);
        if (!empty($search)) {
            $user->orWhere($this->table_name . ".nip", "like", "%$search%")->orWhere($this->table_name . ".nama", "like", "%$search%");
            $this->setData("q_search", $search);
        }
        $filter = $this->filterQuery($user);
        $sort = $this->sortQuery($user, true);
        if ($inertia_request) {
            $this->setData("q_filter", $filter["list_filter"]);
            $this->setData("q_filter_tag", $filter["list_tag"]);
            // dd($sort);
            if (!empty($sort)) {
                $this->setData("q_sort", $sort);
            }
        }
        return $user;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // $page        = $request->query("page", 1);
        $user = $this->queryIndex();

        // dd("-");
        // $query = str_replace(array('?'), array('\'%s\''), $user->toSql());
        // $query = vsprintf($query, $user->getBindings());
        // dd($query, $this->getSendedData());
        $user = $user->paginate(10);
        // dd($user);
        if ($user->isNotEmpty()) {
            $this->setPaginate($user);

            $data = $user->map(function ($item, $key) {
                $ijin = $item->jabatan->ijin;

                if (empty($ijin)) {
                    $result_ijin = [
                        "r_surat" => false,
                        "r_all_disposisi" => false,
                        "r_laporan" => false,
                        "d_surat" => false,
                        "d_miliksurat" => false,
                        "dp_surat" => false,
                        "w_disposisi" => false,
                        "w_suratmasuk" => false,
                        "w_suratkeluar" => false,
                        "admin" => false,
                        "super_admin" => false,
                    ];
                } else {
                    $result_ijin = $ijin->toArray();
                    $result_ijin["super_admin"] = $item->id == 1;
                }

                return [
                    "id"            => $item->id,
                    "nama"          => $item->nama,
                    "username"      => $item->username,
                    "nip"           => $item->nip,
                    "jabatan"       => $item->jabatan->nama,
                    "ijin"         => $result_ijin,
                ];
            });

            $this->setData("tableData", $data);
            $this->setData("isAvailable", true);
        } else {
            $this->setData("isAvailable", false);
        }

        $this->setTitle("Kelola Pengguna");

        return $this->runInertia("User/Index");
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
        $user = $this->queryIndex(false);
        $user = $user->paginate(10);

        if ($user->isEmpty()) {
            $result["empty"] = true;
        }

        $result["lastPage"]     = $user->lastPage();
        $result["currentPage"]  = $user->currentPage();

        if ($result["currentPage"] > $result["lastPage"]) {
            $result["limit"] = true;
            $result["empty"] = false;
        }
        $data = $user->map(function ($item, $key) {
            $ijin = $item->jabatan->ijin;

            if (empty($ijin)) {
                $result_ijin = [
                    "r_surat" => false,
                    "r_all_disposisi" => false,
                    "r_laporan" => false,
                    "d_surat" => false,
                    "d_miliksurat" => false,
                    "dp_surat" => false,
                    "w_disposisi" => false,
                    "w_suratmasuk" => false,
                    "w_suratkeluar" => false,
                    "admin" => false,
                    "super_admin" => false,
                ];
            } else {
                $result_ijin = $ijin->toArray();
                $result_ijin["super_admin"] = $item->id == 1;
            }

            return [
                "id"            => $item->id,
                "nama"          => $item->nama,
                "username"      => $item->username,
                "nip"           => $item->nip,
                "jabatan"       => $item->jabatan->nama,
                "ijin"          => $result_ijin
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
        $this->setTitle("Buat Pengguna", true);
        if (request()->user()->id != 1) {
            $jabatan = DB::table("jabatan")->where("ijin.admin", "!=", 1)->join("ijin", "ijin.id", "=", "jabatan.id_ijin")->select(["jabatan.id", "jabatan.nama"])->get();
        } else {
            $jabatan = DB::table("jabatan")->select(["id", "nama"])->get();
        }
        $this->setData("listJabatan", $jabatan);
        return $this->runInertia("User/Create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $is_super_admin = $request->user()->id == 1 ?? 0;
        // $is_admin = $request->user()->jabatan->ijin->admin == 1 ?? 0;
        $input = $request->input();
        $validator = Validator::make($input, [
            "username" => "required|string|min:3|max:70|regex:/^[0-9A-Za-z.\-_]+$/",
            "password" => "required|string|min:8|max:50",
            "cpassword" => "required|string|min:8|max:50|same:password",
            "nama" => "required|string|max:255",
            "nip" => "required|string|size:18",
            "no_telepon" => "required|string|min:8|max:18",
            "id_jabatan" => "required|exists:jabatan,id",
        ]);
        // dd($validator->errors(), $validator->fails());
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format pada data form yang dimasukkan.");
            return $this->redirectInertia(route("setting.users.create"), $toast);
        }

        if ($input['id_jabatan'] == 1 && !$is_super_admin) {
            $toast = Toast::error("Gagal", "Anda tidak diijinkan untuk melakukan tindakan ini.");
            return $this->redirectInertia(route("setting.users.create"), $toast);
        }
        $user = User::where("username", $input["username"])->first();
        if (!empty($user)) {
            $toast = Toast::error("Gagal", "Username yang anda masukkan telah dipakai.");
            return $this->redirectInertia(route("setting.users.create"), $toast);
        }
        DB::beginTransaction();
        $user                = new User;
        $user->username      = strtolower($input["username"]);
        $user->password      = bcrypt($input["password"]);
        $user->nama          = $input["nama"];
        $user->nip           = $input["nip"];
        $user->no_telpon     = $input["no_telepon"];
        $user->id_jabatan    = $input["id_jabatan"];
        $user->save();
        DB::commit();

        $toast = Toast::success("Sukses", "Berhasil menambahkan pengguna!");

        // session(["temp_file" => null]);

        return $this->redirectInertia(route("setting.users.show", ["user" => $user->id, "username" => $user->username]), $toast);
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
    public function show(User $user, $username)
    {
        if ($user->username === $username) {
            $this->redirectInertia("404");
        }
        $ijin = $user->jabatan->ijin;
        if (empty($ijin)) {
            $result_ijin = [
                "nama" => "Tidak Terdefinisi",
                "r_surat" => false,
                "r_all_disposisi" => false,
                "r_laporan" => false,
                "d_surat" => false,
                "d_miliksurat" => false,
                "dp_surat" => false,
                "w_disposisi" => false,
                "w_suratmasuk" => false,
                "w_all_surat" => false,
                "w_suratkeluar" => false,
                "admin" => false,
                "super_admin" => false,
            ];
        } else {
            $result_ijin = $ijin->toArray();
            $result_ijin["super_admin"] = $user->id == 1;
        }

        $data = [
            "id"             => $user->id,
            "username"       => strtolower($user->username),
            "nama"           => $user->nama,
            "nip"            => $user->nip,
            "no_telepon"     => $user->no_telpon,
            "id_jabatan"     => $user->id_jabatan,
            "jabatan"        => $user->jabatan->nama,
            "ijin"           => $result_ijin
        ];
        if (request()->user()->id != 1) {
            $jabatan = DB::table("jabatan")->where("ijin.admin", "!=", 1)->join("ijin", "ijin.id", "=", "jabatan.id_ijin")->select(["jabatan.id", "jabatan.nama"])->get();
        } else {
            $jabatan = DB::table("jabatan")->select(["id", "nama"])->get();
        }
        $this->setData("listJabatan", $jabatan);

        $this->setTitle("Detail Pengguna", true);
        $this->setData("detailData", $data);
        return $this->runInertia("User/Show");
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
    public function update(Request $request, User $user, $username)
    {
        $is_super_admin = $request->user()->id == 1 ?? 0;
        $is_admin = $request->user()->jabatan->ijin->admin == 1 ?? 0;
        if ($user->username != $username) {
            $this->redirectInertia("404");
        }
        if ($user->jabatan->id == 1 && $is_admin) {
            $toast = Toast::error("Gagal", "Anda tidak diijinkan untuk melakukan tindakan ini.");
            return $this->redirectInertia(route("setting.users.show", ["user" => $user->id, "username" => $user->username]), $toast);
        }
        $input = $request->input();
        if ($is_admin) {
            $rules = [
                "nama" => "required|string|max:254",
                "nip" => "required|string|size:18",
                "no_telepon" => "required|string|max:18|min:10",
                "id_jabatan" => "required|exists:jabatan,id",
                "username" => "required|regex:/^[0-9A-Za-z.\-_]+$/|min:3|max:70"
            ];
        } else {
            $rules = [
                "nama" => "required|string|max:254",
                "nip" => "required|date|before_or_equal:now",
                "no_telepon" => "required|string|max:18|min:10",
            ];
        }
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format pada data form yang dimasukkan.");
            return $this->redirectInertia(route("setting.users.show", ["user" => $user->id, "username" => $user->username]), $toast);
        }

        if ($input['id_jabatan'] == 1 && !$is_super_admin) {
            $toast = Toast::error("Gagal", "Anda tidak diijinkan untuk melakukan tindakan ini.");
            return $this->redirectInertia(route("setting.users.show", ["user" => $user->id, "username" => $user->username]), $toast);
        }
        $username_same = User::where("username", $input["username"])->first();
        if (!empty($username_same) && $username_same->id != $user->id) {
            $toast = Toast::error("Gagal", "Username yang anda masukkan telah dipakai.");
            return $this->redirectInertia(route("setting.users.show", ["user" => $user->id, "username" => $user->username]), $toast);
        }


        DB::beginTransaction();
        $user->nama          = $input["nama"];
        $user->nip           = $input["nip"];
        $user->no_telpon     = $input["no_telepon"];
        if ($is_admin) {
            $user->username   = strtolower($input["username"]);
            $user->id_jabatan = $input["id_jabatan"];
        }
        $user->save();
        DB::commit();
        if ($user->wasChanged()) {
            $toast = Toast::success("Sukses", "Berhasil mengedit data pengguna!");
        } else {
            $toast = Toast::success("Sukses", "Tidak ada yang berubah.");
        }

        return $this->redirectInertia(route("setting.users.show", ["user" => $user->id, "username" => $user->username]), $toast);
    }

    public function updatePassword(Request $request, User $user, $username)
    {
        $is_super_admin = $request->user()->id == 1 ?? 0;
        $is_admin = $request->user()->jabatan->ijin->admin == 1 ?? 0;
        if ($user->username != $username) {
            $this->redirectInertia("404");
        }
        if ($user->jabatan->id == 1 && $is_admin) {
            $toast = Toast::error("Gagal", "Anda tidak diijinkan untuk melakukan tindakan ini.");
            return $this->redirectInertia(route("setting.users.show", ["user" => $user->id, "username" => $user->username]), $toast);
        }
        $input = $request->input();
        $rules = [
            "password" => "required|string|min:8|max:50",
            "cpassword" => "required|string|min:8|max:50|same:password",
        ];
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $toast = Toast::error("Gagal", "Terjadi kesalahan format pada data form yang dimasukkan.");
            return $this->redirectInertia(route("setting.users.show", ["user" => $user->id, "username" => $user->username]), $toast);
        }

        DB::beginTransaction();
        $user->password      = bcrypt($input["password"]);
        $user->save();
        DB::commit();
        if ($user->wasChanged()) {
            $toast = Toast::success("Sukses", "Berhasil mengedit password pengguna!");
        } else {
            $toast = Toast::success("Sukses", "Tidak ada yang berubah.");
        }

        return $this->redirectInertia(route("setting.users.show", ["user" => $user->id, "username" => $user->username]), $toast);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user, $username)
    {
        if ($user->username === $username) {
            $this->redirectInertia("404");
        }
        if ($user->id == 1 || ($user->jabatan->id == 1 && request()->user()->id != 1)) {
            $toast = Toast::error("Gagal Menghapus", "Tindakan tidak di ijinkan.");
            return $this->redirectInertia(route("setting.users.index"), $toast);
        }
        $user->delete();
        $toast = Toast::success("Hapus Pengguna", "Penghapusan pengguna berhasil!");
        return $this->redirectInertia(route("setting.users.index"), $toast);
    }

    public function profile(User $user, $username)
    {
        if ($user->username === $username) {
            $this->redirectInertia("404");
        }
        $ijin = $user->jabatan->ijin;
        if (empty($ijin)) {
            $result_ijin = [
                "nama" => "Tidak Terdefinisi",
                "r_surat" => false,
                "r_all_disposisi" => false,
                "r_laporan" => false,
                "d_surat" => false,
                "d_miliksurat" => false,
                "dp_surat" => false,
                "w_disposisi" => false,
                "w_suratmasuk" => false,
                "w_all_surat" => false,
                "w_suratkeluar" => false,
                "admin" => false,
                "super_admin" => false,
            ];
        } else {
            $result_ijin = $ijin->toArray();
            $result_ijin["super_admin"] = $user->id == 1;
        }

        $data = [
            "id"             => $user->id,
            "username"       => strtolower($user->username),
            "nama"           => $user->nama,
            "nip"            => $user->nip,
            "no_telepon"     => $user->no_telpon,
            "id_jabatan"     => $user->id_jabatan,
            "jabatan"        => $user->jabatan->nama,
            "ijin"           => $result_ijin
        ];
        if (request()->user()->id != 1) {
            $jabatan = DB::table("jabatan")->where("ijin.admin", "!=", 1)->join("ijin", "ijin.id", "=", "jabatan.id_ijin")->select(["jabatan.id", "jabatan.nama"])->get();
        } else {
            $jabatan = DB::table("jabatan")->select(["id", "nama"])->get();
        }

        $this->setData("listJabatan", $jabatan);

        $this->setTitle("Detail Pengguna", true);
        $this->setData("detailData", $data);
        $this->setData("isProfile", true);
        return $this->runInertia("User/Show");
    }
}
