<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SuratMasuk extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'suratmasuk';
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $casts = [
        "tanggal_surat" => "datetime",
    ];
    protected static function booted()
    {
        static::saving(function ($surat_masuk) {
            $surat_masuk->local_created_at = Carbon::now()->timezone("Asia/Jayapura");
        });
    }

    public function setTanggalSuratAttribute($value)
    {
        // $this->casts;
        if (is_string($value) && Carbon::hasFormat($value, "d/m/Y"))
            $this->attributes['tanggal_surat'] = Carbon::createFromFormat("d/m/Y", $value);
        else
            $this->attributes['tanggal_surat'] = $value;
    }


    public function scopeConfigTable($query, $request)
    {
        $sort = $request->query('sortColumn');
        $search = $request->query('search');
        if (!empty($sort)) {
            // dd($column);
            $sort = Str::snake($sort);
            $query->orderBy($sort, $request->query('sortBy'));
        }
        if (!empty($search)) {
            $column_search = ['no_surat', 'asal_surat'];
            foreach ($column_search as $key => $value) {
                $query->orWhere($value, 'like', "%$search%");
            }
        }
        return $query;
    }

    public function scopeWhereNoSurat($query, $no_surat)
    {
        return $query->where("no_surat", "like", "%{$no_surat}%");
    }

    public function sifatSurat()
    {
        return $this->belongsTo(SifatSurat::class, "id_sifat");
    }

    public function dokumen()
    {
        return $this->hasMany(DokumenSuratMasuk::class, "id_suratmasuk");
    }
    public function user()
    {
        return $this->belongsTo(User::class, "id_pembuat");
    }
}
