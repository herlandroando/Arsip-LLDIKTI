<?php

namespace App\Http\Requests;

use App\Http\Controllers\Toast;
use Illuminate\Foundation\Http\FormRequest;

class StoreSuratMasukRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "perihal" => "required|string|max:254",
            "tanggal_surat" => "required|date|before_or_equal:now",
            "id_sifat" => "required|exists:sifatsurat,id|max:500",
            "no_surat" => "required|string|max:100",
            "asal_surat" => "required|string|max:500",
            "isi_ringkas" => "required|string|max:500",
        ];
    }
}
