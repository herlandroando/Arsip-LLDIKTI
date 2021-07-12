<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SuratKeluar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'suratkeluar';
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function setTanggalSuratAttribute($value)
    {
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
            $sort = Str::snake($sort);
            $query->orderBy($sort, $request->query('sortBy'));
        }
        if (!empty($search)) {
            $column_search = ['no_surat', 'tujuan'];
            foreach ($column_search as $key => $value) {
                $query->orWhere($value, 'like', "%$search%");
            }
        }
        return $query;
    }

    public function sifatSurat()
    {
        return $this->belongsTo(SifatSurat::class, "id_sifat");
    }

    public function jabatan(){
        return $this->belongsTo(Jabatan::class, "asal_surat");
    }

    public function dokumen()
    {
        return $this->hasMany(DokumenSuratKeluar::class, "id_suratkeluar");
    }

    // public function profilM()
    // {
    //     return $this->hasMany(Profil::class,"id_profil");
    // }
}
