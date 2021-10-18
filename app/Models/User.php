<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'pengguna';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function profil()
    {
        return $this->hasOne(Profil::class,"id_user");
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class,"id_jabatan");
    }

    public function surat_masuk()
    {
        return $this->hasMany(SuratMasuk::class,"id_pembuat");
    }

    public function surat_keluar()
    {
        return $this->hasMany(SuratKeluar::class,"id_pembuat");
    }
}
