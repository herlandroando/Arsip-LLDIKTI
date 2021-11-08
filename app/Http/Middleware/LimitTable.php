<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Limit the pagination table up to set of max pagination only.
 */
class LimitTable
{
    /**
     * Max of pagination table.
     *
     * @var integer
     */
    public $max_pagination = 10;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->has("page") && intval($request->query("page")) > $this->max_pagination) {
            $page = intval($request->query("page"));
            $uri = str_replace("page=$page", "page=$this->max_pagination", $request->getRequestUri());
            return redirect(url($uri));
        }

        return $next($request);
    }
}
