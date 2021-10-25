<?php

use App\Models\SuratKeluar;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', "HomeController@index")->name("home")->middleware("auth");
Route::get('/login', "LoginController@index")->name("login");
Route::post('/login', "LoginController@login")->name("login.post");
Route::get('/logout', "LoginController@logout")->name("logout")->middleware("auth");

Route::middleware("permission:inbox.r")->prefix("manage")->name('manage.')->group(function () {
    Route::prefix("inbox")->name('inbox.')->group(function () {
        Route::get('/download/{id}/{name}', "ManageMail\InboxController@download")->name("download");
        Route::get('/option', "ManageMail\InboxController@filterOptions")->name("option");

        Route::post("/upload/temp", "ManageMail\InboxController@uploadTemp")->name("upload.temp")->middleware("permission:inbox.w");
        Route::post("/delete/temp", "ManageMail\InboxController@deleteTemp")->name("delete.temp")->middleware("permission:inbox.w");

        Route::get('/create', "ManageMail\InboxController@create")->name("create")->middleware("permission:inbox.w");
        Route::post('/create', "ManageMail\InboxController@store")->name("store")->middleware("permission:inbox.w");

        Route::get('/json', "ManageMail\InboxController@jsonIndex")->name("index.json");
        Route::get('/', "ManageMail\InboxController@index")->name("index");

        Route::post("{surat_masuk}/upload/file", "ManageMail\InboxController@uploadFile")->name("upload.file")->middleware("permission:inbox.w");;
        Route::post("{surat_masuk}/delete/file", "ManageMail\InboxController@deleteFile")->name("delete.file")->middleware("permission:inbox.w");;

        Route::delete('/{surat_masuk}', "ManageMail\InboxController@destroy")->name("destroy")->middleware("permission:inbox.d");
        Route::put('/{surat_masuk}', "ManageMail\InboxController@update")->name("update")->middleware("permission:inbox.w");
        Route::get('/{surat_masuk}', "ManageMail\InboxController@show")->name("show");
    });
    Route::middleware("permission:send.r")->prefix("send")->name('send.')->group(function () {
        Route::get('/download/{id}/{name}', "ManageMail\SendController@download")->name("download");
        Route::get('/option', "ManageMail\SendController@filterOptions")->name("option");
        Route::get('/search/username', "ManageMail\SendController@searchUsername")->name("search.username");
        Route::post("/upload/temp", "ManageMail\SendController@uploadTemp")->name("upload.temp")->middleware("permission:send.w");
        Route::post("/delete/temp", "ManageMail\SendController@deleteTemp")->name("delete.temp")->middleware("permission:send.w");
        Route::get('/create', "ManageMail\SendController@create")->name("create")->middleware("permission:send.w");
        Route::post('/create', "ManageMail\SendController@store")->name("store")->middleware("permission:send.w");
        Route::get('/json', "ManageMail\SendController@jsonIndex")->name("index.json");
        Route::get('/', "ManageMail\SendController@index")->name("index");
        Route::post("{surat_keluar}/upload/file", "ManageMail\SendController@uploadFile")->name("upload.file")->middleware("permission:send.w");
        Route::post("{surat_keluar}/delete/file", "ManageMail\SendController@deleteFile")->name("delete.file")->middleware("permission:send.w");
        Route::delete('/{surat_keluar}', "ManageMail\SendController@destroy")->name("destroy")->middleware("permission:send.d");
        Route::put('/{surat_keluar}', "ManageMail\SendController@update")->name("update")->middleware("permission:send.w");
        Route::get('/{surat_keluar}', "ManageMail\SendController@show")->name("show");
    });
    Route::middleware("permission:disposisi.r")->prefix("disposisi")->name('disposisi.')->group(function () {
        Route::get('/list/surat', "ManageMail\DisposisiController@getListSuratMasuk")->name("list.surat");
        Route::get('/option', "ManageMail\DisposisiController@filterOptions")->name("option");
        Route::get('/create', "ManageMail\DisposisiController@create")->name("create")->middleware("permission:disposisi.w");
        Route::post('/create', "ManageMail\DisposisiController@store")->name("store")->middleware("permission:disposisi.w");
        Route::get('/', "ManageMail\DisposisiController@index")->name("index");
        Route::post('/{disposisi}/activity', "ManageMail\DisposisiController@createActivity")->name("activity.create");
        Route::put('/{disposisi}/status', "ManageMail\DisposisiController@updateStatus")->name("status.update");
        Route::get('/{disposisi}', "ManageMail\DisposisiController@show")->name("show");
        Route::put('/{disposisi}', "ManageMail\DisposisiController@update")->name("update")->middleware("permission:disposisi.w");
        Route::delete('/{disposisi}', "ManageMail\DisposisiController@destroy")->name("destroy")->middleware("permission:disposisi.d");
    });
});

Route::prefix("report")->name("report.")->middleware("permission:report.r")->group(function () {
    Route::get('download/{id}/{name}', "ReportController@download")->name("download");
    Route::get('/', "ReportController@index")->name("index");
    Route::get('/json', "ReportController@jsonIndex")->name("index.json");
});

Route::prefix("recycle")->name("recycle.")->middleware("permission:recycle.r")->group(function () {
    Route::prefix("inbox")->name('inbox.')->group(function () {
        Route::get("/", "Recycle\InboxController@index")->name("index");
        Route::put("/{surat_masuk}", "Recycle\InboxController@restore")->name("restore");
        Route::delete("/{surat_masuk}", "Recycle\InboxController@destroy")->name("destroy");
    });
    Route::prefix("send")->name('send.')->group(function () {
        Route::get("/", "Recycle\SendController@index")->name("index");
        Route::put("/{surat_keluar}", "Recycle\SendController@restore")->name("restore");
        Route::delete("/{surat_keluar}", "Recycle\SendController@destroy")->name("destroy");
    });
});

Route::get('profile/{user}/{username}', "Setting\UserController@profile")->name("profile");

Route::prefix("setting")->name("setting.")->group(function () {
    Route::middleware("auth")->prefix("users")->name("users.")->group(function () {
        Route::get('/create', "Setting\UserController@create")->name("create")->middleware("permission:admin.r");
        Route::post('/create', "Setting\UserController@store")->name("store")->middleware("permission:admin.r");
        Route::get('/json', "Setting\UserController@jsonIndex")->name("index.json")->middleware("permission:admin.r");
        Route::get('/option', "Setting\UserController@filterOptions")->name("option")->middleware("permission:admin.r");
        Route::get('/', "Setting\UserController@index")->name("index")->middleware("permission:admin.r");
        Route::get('/{user}/{username}', "Setting\UserController@show")->name("show");
        Route::put('/{user}/{username}', "Setting\UserController@update")->name("update")->middleware("permission:admin.r");
        Route::put('/{user}/{username}/password', "Setting\UserController@updatePassword")->name("update.password")->middleware("permission:admin.r");
        Route::delete('/{user}/{username}', "Setting\UserController@destroy")->name("destroy")->middleware("permission:admin.r");
    });
    Route::middleware("permission:admin.r")->group(function () {
        Route::prefix("permission")->name("permission.")->group(function () {
            Route::get('/create', "Setting\PermissionController@create")->name("create");
            Route::post('/create', "Setting\PermissionController@store")->name("store");
            Route::get('/', "Setting\PermissionController@index")->name("index");
            Route::get('/{permission}', "Setting\PermissionController@show")->name("show");
            Route::put('/{permission}', "Setting\PermissionController@update")->name("update");
            Route::delete('/{permission}', "Setting\PermissionController@destroy")->name("destroy");
        });
        Route::prefix("jabatan")->name("jabatan.")->group(function () {
            Route::get('/create', "Setting\JabatanController@create")->name("create");
            Route::post('/create', "Setting\JabatanController@store")->name("store");
            Route::get('/', "Setting\JabatanController@index")->name("index");
            Route::get('/{jabatan}', "Setting\JabatanController@show")->name("show");
            Route::put('/{jabatan}', "Setting\JabatanController@update")->name("update");
            Route::delete('/{jabatan}', "Setting\JabatanController@destroy")->name("destroy");
        });
        Route::prefix("advance")->name("advance.")->group(function () {
            Route::get('/', "Setting\AdvanceController@index")->name("index");
        });
    });
});


Route::get('test', function () {
    // return inertia("Test/Test")->withViewData("_sidebars", [])
    //     ->withViewData("_sifat_surat", [])
    //     ->withViewData("_bagianInstansi", []);
    // $carbon=Carbon::parse("17-09-2021 15:00:00")->timezone("Asia/Jayapura");
    // dd($carbon->toDayDateTimeString());
});
Route::get("test2", "TestController@index")->name("test.index2");

Route::get("error/{code}", "ErrorController@index")->name("errors.index");
