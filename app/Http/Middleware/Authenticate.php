<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Toast;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            $toast = new Toast(Toast::ERROR, "Tidak Diijinkan", "Anda belum masuk sebagai pengguna!");
            $request->session()->flash("_toast", $toast->toArray());
            return route('login');
        }
    }
}
