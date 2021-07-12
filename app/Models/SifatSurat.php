<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SifatSurat extends Model
{
    use HasFactory;

    protected $table = 'sifatsurat';
    public $timestamps = false;

    public function suratKeluar(){
        $this->hasMany(SuratKeluar::class,"id_sifat");
    }

    public function suratMasuk(){
        $this->hasMany(SuratMasuk::class,"id_sifat");
    }
}
