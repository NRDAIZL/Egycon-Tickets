<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasEventId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(!$request->has('event_id')){
            return redirect()->route('admin.events.index');
        }
        return $next($request);
    }
}
