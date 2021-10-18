<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ErrorController extends Controller
{
    public function index($code = 100)
    {
        switch ($code) {
            case '404':
                $title = "Error - Tidak Ditemukan";

                break;
            case '500':
                $title = "Error - Kesalahan Server";

                break;
            case '400':
                $title = "Error - Tidak Di Ijinkan";

                break;

            default:
                $title = "Tidak Terjadi Apa-Apa";
                break;
        }
        $this->setData("code", $code);
        $this->setTitle($title);
        $this->showTitle(false);
        return $this->runInertia("Error/Index");
    }
}
