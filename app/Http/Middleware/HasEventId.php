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
        // get route parameters
        $routeParameters = $request->route()->parameters();
        // check if event_id is present in route parameters
        if (!isset($routeParameters['event_id'])) {
            return redirect()->route('admin.events.view')->with('error','Event not found!');
        }
        $event_id = $routeParameters['event_id'];
        $user = auth()->user();
        $user_events = $user->events;
        $user_events_ids = $user_events->pluck('id')->toArray();
        if (!in_array($event_id,
            $user_events_ids
        )) {
            return redirect()->route('admin.events.view')->with('error', 'Event not found!');
        }
        setPermissionsTeamId($routeParameters['event_id']);

        $request->attributes->add(['event_id' => $event_id]);
        return $next($request);
    }
}
