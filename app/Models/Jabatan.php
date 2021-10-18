<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $table = 'jabatan';
    public $timestamps = false;

    public function ijin()
    {
        return $this->belongsTo(Ijin::class, "id_ijin");
    }
}
