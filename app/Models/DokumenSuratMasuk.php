<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenSuratMasuk extends Model
{
    use HasFactory;

    protected $table = 'dokumen_sm';
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function suratMasuk()
    {
        return $this->belongsTo(SuratMasuk::class, "id_suratmasuk");
    }
}
