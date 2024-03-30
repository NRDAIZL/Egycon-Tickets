<?php

namespace App\Providers;

use App\Models\Event;
use Illuminate\Support\Facades\Config;
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
        app()->singleton(Event::class, function ($app)  {
            $event_id = request()->route('event_id') ?? request()->route('x_event_id');
            if($event_id == null){
                return;
            }
            if(is_numeric($event_id)){
                $event = Event::find($event_id);
                if($event == null){
                    return;
                }
                return $event;
            }else{
                $event = Event::where('slug',$event_id)->first();
                if($event == null){
                    return;
                }
                return $event;
            }

        });
        // pass event_id and event to all admin views
        view()->composer(['*'], function ($view) {
            $event = app(Event::class);
            $view->with('event_id', request()->route('event_id'))->with('event', $event);
        });
    }
}
