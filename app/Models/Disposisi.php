<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Disposisi extends Model
{
    use HasFactory;

    const SEDANG_DIPROSES = "Sedang Diproses";
    const BELUM_DIPROSES = "Belum Diproses";
    const DITINJAU = "Ditinjau";
    const REVISI = "Revisi";
    const SELESAI = "Selesai";
    const BERAKHIR = "Berakhir";

    protected $option = ["Sedang Diproses", "Belum Diproses", "Ditinjau", "Revisi", "Selesai", "Berakhir"];
    //Belum Diproses > Sedang Diproses > Ditinjau > Revisi/Selesai
    //Berakhir
    protected $casts = ["is_suratmasuk" => "boolean"];
    protected $table = 'disposisi';
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function booted()
    {
        static::saving(function ($disposisi) {
            $disposisi->local_created_at = Carbon::now()->timezone("Asia/Jayapura");
        });
    }

    public function scopeActive($query, int $limit = 10)
    {
        $query = $query->whereNotIn("status", [self::BERAKHIR, self::SELESAI])->orderBy("updated_at", "desc");
        if ($limit > 0) {
            return $query->limit($limit);
        } else {
            return $query;
        }
    }

    public function getIsiRingkasAttribute()
    {
        return Str::limit($this->attributes['isi'], 30, '');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, "id_pengirim");
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, "id_jabatan");
    }

    public function suratmasuk()
    {
        return $this->belongsTo(SuratMasuk::class, "id_suratmasuk");
    }

    public function detailDisposisi()
    {
        return $this->hasMany(DetailDisposisi::class, "id_disposisi");
    }
}
