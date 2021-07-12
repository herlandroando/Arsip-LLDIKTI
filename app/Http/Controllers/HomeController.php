<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->session());
        if (empty($request->user())) {
            $toast = new Toast(Toast::ERROR, "Tidak Diijinkan", "Anda belum masuk sebagai pengguna!");
            return $this->redirectInertia(url("login"), $toast);
        }
        $this->setTitle("Dashboard");
        $this->setIndexActive("dashboard");
        return $this->runInertia("Home/Index");
    }
}
