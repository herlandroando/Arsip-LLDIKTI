<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ijin extends Model
{
    use HasFactory;

    protected $table = 'ijin';
    protected $casts = [
        "r_surat" => "boolean",
        "r_all_disposisi" => "boolean",
        "r_laporan" => "boolean",
        "d_surat" => "boolean",
        "d_miliksurat" => "boolean",
        "dp_surat" => "boolean",
        "w_disposisi" => "boolean",
        "w_suratmasuk" => "boolean",
        "w_suratkeluar" => "boolean",
        "admin" => "boolean",
    ];
    public $timestamps = false;

    public function scopeHasPermission($query, array $keys)
    {
        $valid = false;
        $permission = $query->first();
        if (empty($permission)) {
            return false;
        }
        $permission_array =  $permission->toArray();
        // dd($permission,$permission_array);
        foreach ($keys as $value) {
            if (is_array($value)) {
                $valid = $this->orPermission(collect($permission_array), $value);
            } else {
                if (empty($permission_array[$value])) {
                    return false;
                } else {
                    $valid = $permission_array[$value];
                }
            }
            if (!$valid) {
                return false;
            }
        }
        return true;
    }

    public function orPermission($permission, $keys)
    {
        $only = $permission->only($keys);
        foreach ($keys as $value) {
            if (empty($only[$value])) {
                return false;
            }
            if ($only[$value]) {
                return true;
            }
        }
        return false;
    }
}
