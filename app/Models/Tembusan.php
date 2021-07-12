<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tembusan extends Model
{
    use HasFactory;

    protected $table = 'tembusan';
    public $timestamps = false;
    protected $guarded =['id'];

    public function scopeFillable($query,$name){
        return $query->where("nama",$name);
    }
}
