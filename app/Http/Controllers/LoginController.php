<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd(request()->session());

        return $this->runInertia("Auth/Login");
        // return inertia("Master");
    }

    public function login(Request $request)
    {
        $validate = Validator::make($request->input(), [
            "username" => "required|string|min:2",
            "password" => "required|string|min:8",
        ]);
        if ($validate->fails()) {
            // $toast = new Toast(Toast::ERROR,"Autentikasi Gagal","Cobalah mengecek kembali username dan password anda.");
            Toast::error("Autentikasi Gagal", "Cobalah mengecek kembali username dan password anda.");
            $this->setData("success", false);
            return $this->runInertia("Auth/Login");
        }
        if (Auth::attempt(['username' => $request->input("username"), 'password' => $request->input("password")])) {
            $toast = Toast::success("Autentikasi Berhasil", "Tunggu beberapa detik untuk masuk ke aplikasi.");
            $this->setData("success", true);
            return $this->redirectInertia(route("home"), $toast);
        } else {
            $toast = Toast::error("Autentikasi Gagal", "Username dan Password tidak cocok dengan pengguna yang terdaftar di server ini.");
            $this->setData("success", false);
            return $this->runInertia("Auth/Login", $toast);
        }
        // $toast = new Toast(Toast::SUCCESS, "Autentikasi Berhasil", "Tunggu beberapa detik untuk masuk ke aplikasi.");

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
