<?php

namespace App\Traits;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 *  Handle all feature of table query on frontend like search, filter, and sort.
 *
 *  If this trait declared on parent/controller. Must declared variable `filter_option`, `table_option`, `sort_option`, `table_name` that will be store
 *  for config this trait.
 */
trait HasManageableTableQuery
{
    // "sifat" => ["column" => "id_sifat", "isRelation" => true, "label" => "sifatsurat.nama", "type" => "select"],
    //     "pembuat" => ["column" => "id_pembuat", "isRelation" => true, "label" => "pengguna.nama", "type" => "select"],
    //     "asal_surat" => ["column" => "asal_surat", "type" => "select"],
    //     "tanggal_buat" => ["column" => "created_at", "type" => "date"],
    public function filterQuery(&$query)
    {
        if (empty($this->filter_option)) {
            if (env("APP_DEBUG"))
                $message        = 'Filter option ($filter_option) is not declared on parent.';
            else
                $message        = 'Server Error.';
            throw new Exception($message);
        }
        $filter_added           = ["list_filter" => [], "list_tag" => []];

        $filter_option_keys     = array_keys($this->filter_option);
        $url_query              = request()->only($filter_option_keys);
        foreach ($url_query as $key => $value) {
            $selected_option    = $this->filter_option[$key];
            $selected_option    += $this->table_option[$key];
            if (!empty($selected_option["permitted"]) && !$this->isPermitted($selected_option["permitted"])) {
                return false;
            }
            $function_call      = "filter" . Str::ucfirst($selected_option["type"]);
            // dd($selected_option, $function_call, method_exists($this,$function_call));

            if (method_exists($this, $function_call)) {
                $filter_added["list_filter"][$key] = $this->$function_call($query, $selected_option, $value);
                $this->filterLabel($filter_added["list_tag"], $key, $selected_option, $value);
            } else {
                if (env("APP_DEBUG"))
                    $message        = 'Type of filter is not available.';
                else
                    $message        = 'Server Error.';
                throw new Exception($message);
            }
        }
        return $filter_added;
    }

    public function filterSelect(&$query, $selected_option, $selected_value)
    {
        // $filter_added = [];
        if (!is_array($selected_value)) {
            $selected_value = explode(",", $selected_value);
        }
        // $count = count($selected_value);
        // if ($count > 1)
        $query = $query->whereIn("{$this->table_name}.{$selected_option['column']}", $selected_value);
        // else
        // $query = $query->whereIn($selected_option["column"], $selected_value);
        return $selected_value;
    }

    public function filterDate(&$query, $selected_option, $selected_value)
    {
        $filter_added = [];

        if (!is_array($selected_value)) {
            $date   = Carbon::parse($selected_value);
            $start  = clone $date->startOfDay();
            $end    = clone $date->endOfDay();
            $query  = $query->orWhereBetween("{$this->table_name}.{$selected_option['column']}", [$start, $end]);
        } else {
            $start  = $selected_value[0];
            $end    = $selected_value[1];
            $query  = $query->orWhereBetween("{$this->table_name}.{$selected_option['column']}", [$start, $end]);
        }
        array_push($filter_added, $selected_value);

        return $filter_added;
    }

    public function filterLabel(&$list, $key, $selected_option, $value)
    {
        $default =    [
            "label" => $selected_option["label"],
            "type"  => $selected_option["type"],
            "query" => $key,
        ];
        if (is_array($value) && $selected_option["type"] != "date") {
            foreach ($value as $v) {
                $this->manageLabel($default, $selected_option, $v);
                if (array_key_exists("custom_label", $selected_option)) {
                    if (array_key_exists($v, $selected_option["custom_label"])) {
                        $default['value'] = $selected_option["custom_label"][$v];
                    } else {
                        $default["value"] = $v;
                    }
                } else {
                    $default["value"] = $v;
                }
                array_push($list, $default);
                // dump($list, $value);
            }
        } else {
            $this->manageLabel($default, $selected_option, $value);
            if (array_key_exists("custom_label", $selected_option)) {
                if (array_key_exists($value, $selected_option["custom_label"])) {
                    $default['value'] = $selected_option["custom_label"][$value];
                } else {
                    $default["value"] = $value;
                }
            } else {
                $default["value"] = $value;
            }
            array_push($list, $default);
            // dump($list, $value);
        }
    }

    public function manageLabel(&$default, $selected_option, $value)
    {
        if (!empty($selected_option["relation"])) {
            list($table, $column) = explode(".", $selected_option["label_column"]);
            $label = DB::table($table)->select(["id", $column])->where("id", $value)->first();
            if (empty($label)) {
                $default["label_value"] = "Tidak Terdefinisi";
            } else {
                $default["label_value"] = $label->$column;
            }
        } else {
            $default["label_value"] = $value;
        }
    }

    public function isPermitted($role)
    {
        $user = request()->user();
        if (empty($user)) {
            return false;
        }
        $user_role = $user->jabatan->ijin->first()->toArray();
        foreach ($user_role as $value) {
            $result = $role[$value];
        }
        return $result === 1;
    }

    public function defaultSelect(&$query)
    {
        return $query->select([$this->table_name . ".*"]);
    }

    public function sortQuery(&$query, $default_select = false)
    {
        $sort_query = request()->query("sort", "");
        if (empty($sort_query)) {
            return;
        }
        if (empty($this->sort_available)) {
            throw new Exception('Option for sort is not defined. You need to declare the variable $sort_available.');
        }

        $sort_keys = array_keys($this->sort_available);

        list($order, $query_key) = explode("!", $sort_query);
        // dd($order,$query_key,$sort_query,$sort_keys);

        if (!in_array($query_key, $sort_keys)) {
            return;
        }

        if (in_array($query_key, $sort_keys)) {
            $selected_option = $this->table_option[$query_key];
            $selected_column = $this->sort_available[$query_key];
            // dd("test", $selected_option, $selected_column);

            if (!empty($selected_option["relation"]) && $selected_option["relation"]) {
                // list($relation_table, $relation) = explode(".", $selected_option["relation"]);
                // dd($order, $query_key, $sort_keys);
                $query = $query->join($selected_option["relation"], "{$selected_option['relation']}.id", "=", "{$this->table_name}.{$selected_option['column']}");
                $query = $query->orderBy("{$selected_option["relation"]}.{$selected_column}", $order);
            } else {
                $query = $query->orderBy("{$this->table_name}.{$selected_column}", $order);
            }
        }

        if ($default_select) {
            $query = $this->defaultSelect($query);
        }
        // dd($query);
        return ["prop" => $query_key, "order" => "asc" ? "ascending" : "descending"];
    }
}
