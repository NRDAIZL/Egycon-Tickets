<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $roles = explode("|",$role);
        $has_permission = false;
        foreach($roles as $role){
            if(auth()->user()->hasRole($role)){
                $has_permission = true;
            }
        }
        if(!$has_permission){
            return redirect()->route('admin.home', getPermissionsTeamId())->with('error', 'You do not have permission to access this page!');
        }
        return $next($request);
    }
}
