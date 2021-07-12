<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenSuratKeluar extends Model
{
    use HasFactory;

    protected $table = 'dokumen_sk';
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function suratKeluar()
    {
        return $this->belongsTo(SuratKeluar::class, "id_suratkeluar");
    }
}
