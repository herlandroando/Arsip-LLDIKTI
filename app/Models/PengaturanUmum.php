<?php

namespace App\Models;

use App\Exceptions\RenderException;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PengaturanUmum extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_umum';
    public $timestamps = false;

    public $settings = ["delete_mail_not_permanent" => "boolean", "auto_delete_retensi_mail" => "boolean", "retensi" => "integer"];

    public function scopeGetSetting($query, $key)
    {
        $this->hasSettingKey($key);
        $value = $query->where("nama", $key)->first();
        switch ($this->settings[$key]) {
            case 'boolean':
                return $this->getSettingBoolean($value->nilai);
                break;
            case 'integer':
                return $this->getSettingInteger($value->nilai);
                break;
            default:
                return false;
                break;
        }
    }

    public function scopeSetSetting($query,$key, $value)
    {
        $this->hasSettingKey($key);
        $pengaturan_umum = $query->where("nama", $key)->first();
        $pengaturan_umum->nilai = $value;
        $pengaturan_umum->save();
    }

    public function scopeGetCollection($camel_case_key = false)
    {
        $data = [];
        $pengaturan_umum = PengaturanUmum::whereIn("nama", array_keys($this->settings))->get()->pluck("nilai", "nama");
        foreach ($pengaturan_umum as $key => $value) {
            if ($camel_case_key) {
                $key = Str::snake($key);
            }
            // dd($key);
            switch ($this->settings[$key]) {
                case 'boolean':
                    $data[$key] = $this->getSettingBoolean($value);
                    break;
                case 'integer':
                    $data[$key] = $this->getSettingInteger($value);
                    break;
                default:
                    return false;
                    break;
            }
        }
        return $data;
    }

    public function getSettingBoolean($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function getSettingInteger($value)
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    public function hasSettingKey($key, $is_throw = true)
    {
        // dd($key, $this->settings);
        if (!in_array($key, array_keys($this->settings))) {
            // if (env("APP_DEBUG", true)) {
            //     // dd("ss");
            // } else {
            //     return redirect(route("errors.index", ["code" => 500]));
            // }
            if ($is_throw)
                throw new Exception("Error Processing Settings. Key not found.");
            else {
                return false;
            }
        }
        return true;
    }
}
