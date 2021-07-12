<?php

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

Route::get('/', "HomeController@index")->name("home");
Route::get('/login', "LoginController@index")->name("login");
Route::post('/login', "LoginController@login")->name("login.post");

Route::prefix("manage")->name('manage.')->group(function () {
    Route::prefix("inbox")->name('inbox.')->group(function () {
        Route::post("/upload/temp", "ManageMail\InboxController@uploadTemp")->name("upload.temp");
        Route::post("/delete/temp", "ManageMail\InboxController@deleteTemp")->name("delete.temp");
        Route::get('/create', "ManageMail\InboxController@create")->name("create");
        Route::post('/create', "ManageMail\InboxController@store")->name("store");
        Route::get('/', "ManageMail\InboxController@index")->name("index");
        Route::post("{surat_masuk}/upload/file", "ManageMail\InboxController@uploadFile")->name("upload.file");
        Route::post("{surat_masuk}/delete/file", "ManageMail\InboxController@deleteFile")->name("delete.file");
        Route::delete('/{surat_masuk}', "ManageMail\InboxController@destroy")->name("destroy");
        Route::put('/{surat_masuk}', "ManageMail\InboxController@update")->name("update");
        Route::get('/{surat_masuk}', "ManageMail\InboxController@show")->name("show");
    });
    Route::prefix("send")->name('send.')->group(function () {
        Route::post("/upload/temp", "ManageMail\SendController@uploadTemp")->name("upload.temp");
        Route::post("/delete/temp", "ManageMail\SendController@deleteTemp")->name("delete.temp");
        Route::get('/create', "ManageMail\SendController@create")->name("create");
        Route::post('/create', "ManageMail\SendController@store")->name("store");
        Route::get('/', "ManageMail\SendController@index")->name("index");
        Route::post("{surat_keluar}/upload/file", "ManageMail\SendController@uploadFile")->name("upload.file");
        Route::post("{surat_keluar}/delete/file", "ManageMail\SendController@deleteFile")->name("delete.file");
        Route::delete('/{surat_keluar}', "ManageMail\SendController@destroy")->name("destroy");
        Route::put('/{surat_keluar}', "ManageMail\SendController@update")->name("update");
        Route::get('/{surat_keluar}', "ManageMail\SendController@show")->name("show");
    });
});
