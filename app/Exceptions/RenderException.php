<?php

namespace App\Exceptions;

use App\Models\Jabatan;
use App\Models\SifatSurat;
use Exception;


class RenderException extends Exception
{
    protected $code;
    protected $debug_mode;
    protected $message;
    /**
     * Exception handling on Route Name.
     *
     * @param int $code
     */
    public function __construct(bool $debug_mode = false, string $message = "", int $code)
    {
        $this->code = $code;
        $this->debug_mode = $code;
        $this->message = $message;
    }

    public function render($request)
    {
        if ($this->debug_mode && env("APP_DEBUG", true)) {
            throw new Exception($this->message);
        } else {
            return redirect(route("errors.index", ["code" => $this->code]));
        }
    }
}
