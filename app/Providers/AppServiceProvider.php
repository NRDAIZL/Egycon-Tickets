<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // pass event_id to all admin views
        view()->composer(['*'], function ($view) {
            // check if user has access to event
            if (request()->route('event_id')) {
                $event_id = request()->route('event_id');
                $user = auth()->user();
                $user_events = $user->events;
                $user_events_ids = $user_events->pluck('id')->toArray();
                if (!in_array($event_id, $user_events_ids)) {
                    return abort(403);
                }
            }
            $view->with('event_id', request()->route('event_id'));
        });
    }
}
