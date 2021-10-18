<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailDisposisi extends Model
{
    use HasFactory;

    protected $table = 'detail_disposisi';
    protected $casts = [
        "is_update_status" => "boolean"
    ];
    public $timestamps = false;


    public function disposisi()
    {
        return $this->belongsTo(Disposisi::class, "id_disposisi");
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, "id_pembuat");
    }
}
