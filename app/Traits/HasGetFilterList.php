<?php

namespace App\Traits;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Handle filter list on frontend. If use `filter-container` component on frontend, select and autocomplete type
 * option is handle on this trait.
 *
 * Just add on route, for example
 * ```php
 * Route::get('route_name', "Pick/Class/Controller/Option@filterOptions")->name("bla.bla.option");
 * ```
 *
 * and add one variable. Named it `$filter_list_option`.
 * a example will show at file docs at
 *
 *
 * @author Herlandro T. <herlandrotri@gmail.com>
 */
trait HasGetFilterList
{
    /**
     * Handle filter's option on the page.
     *
     * @return void
     */
    public function filterOptions()
    {
        if (empty($this->filter_list_option)) {
            if (env("APP_DEBUG"))
                $message = 'Filter list option ($filter_list_option) is not declared on parent.';
            else
                $message = 'Server Error.';
            return response()->json(['message' => $message], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        // dd("");
        $query              = request()->query("query");
        $filtered_query     = in_array($query, array_keys($this->filter_list_option));
        $seachable          = false;
        // dd(array_keys($this->filter_list_option));
        if (empty($filtered_query)) {
            return response()->json(['message' => "Query of filter is not found."], Response::HTTP_NOT_FOUND);
        } else {
            if (request()->has("search")) {
                $seachable  = true;
            }
            $option         = $this->filter_list_option[$query];
            $result         = $this->queryList($option, $seachable);
            return response()->json(['data' => $result, 'message' => "Success get filter list."]);
        }
        //tanggal,sifat,nama,asal

    }

    /**
     * Handle query for list of the option.
     *
     * @param [type] $option
     * @param [type] $model_query
     * @return void
     */
    // "sifat_surat" => ["table" => "sifat_surat", "only" => ["nama", "id"]],
    // "nama_pembuat" => ["table" => "surat_masuk","relation"=>"pengguna.id_pembuat", "column" => 'nama', "only" => ["pengguna.nama", "pengguna.id"]],
    // "asal_surat" => ["table" => "surat_masuk", "column" => 'asal_surat', distinct = true],
    public function queryList($option, $seachable = false)
    {
        $column             = "*";
        $table              = $option["table"];
        $query              = DB::table($table);

        if (!empty($option['column'])) {
            $column         = is_array($option['column']) ? $option['column'] : [$option['column']];
        }
        if ($seachable) {
            $search         = request()->query("search");
            foreach ($column as $key => $value) {
                $query          = $query->where($value, "like", "%{$search}%");
            }
        }
        if (!empty($option["distinct"]) && $option["distinct"]) {
            $query          = $query->distinct();
        }
        if (!empty($option["relation"])) {
            list($relation, $relation_column)   = explode(".", $option["relation"]);
            $query                              = $query->join($relation, "{$relation}.id", "=", "{$table}.{$relation_column}");
        }
        if (!empty($option["only"])) {
            $column         = is_array($option['only']) ? $option['only'] : [$option['only']];
        }

        return $query->get($column);
    }
}
