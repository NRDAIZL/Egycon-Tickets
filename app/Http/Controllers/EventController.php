<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $user_events = auth()->user()->events;
        return view('admin.events.index',['events'=> $user_events]);
    }

    public function add($id = null)
    {
        if($id){
            $event = auth()->user()->events()->where('event_id',$id)->first();
            return view('admin.events.add',['event'=>$event]);
        }
        return view('admin.events.add');
    }
  

    public function store(Request $request){
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'location' => 'nullable',
            'registration_start' => 'required|date',
            'registration_end' => 'required|date',
            'registration_start_time' => 'required',
            'registration_end_time' => 'required',
        ]);
        $slug = Str::slug($request->name);
        // check if slug already exists
        $slug_count = auth()->user()->events()->where('slug',$slug)->count();
        if($slug_count > 0){
            $slug = $slug . '-' . $slug_count;
        }
        // validate registration start and end time
        $registration_start = $request->registration_start . ' ' . $request->registration_start_time;
        $registration_end = $request->registration_end . ' ' . $request->registration_end_time;
        if(strtotime($registration_start) > strtotime($registration_end)){
            return back()->withInput()->with('error', 'Registration start time cannot be greater than registration end time!');
        }
        // store logo and banner
        if($request->has('logo')){
            $logo = $request->logo->store('public/logos');
        }
        if($request->has('banner')){
            $banner = $request->banner->store('public/banners');
        }
        // check if event is being edited
        if($request->has('event_edit_id')){
            $event = auth()->user()->events()->where('event_id',$request->event_edit_id)->first();
            $event->update([
                'name' => $request->name,
                'description' => $request->description??null,
                'location' => $request->location??null,
                'logo' => $logo ?? $event->logo,
                'banner' => $banner ?? $event->banner,
                'registration_start' => $registration_start,
                'registration_end' => $registration_end,
                'slug' => $slug ?? $event->slug,
            ]);
            return redirect()->route('admin.events.view');
        }
        // create event
        $event = auth()->user()->events()->create([
            'name' => $request->name,
            'description' => $request->description??null,
            'location' => $request->location??null,
            'logo' => $logo ?? null,
            'banner' => $banner ?? null,
            'registration_start' => $registration_start,
            'registration_end' => $registration_end,
            'slug' => $slug,
        ]);
        setPermissionsTeamId($event->id);
        $user = auth()->user();
        $user->assignRole('admin');

        return redirect()->route('admin.event_settings.event_days',['event_id'=>$event->id]);
    }


    // Event Settings
    public function edit_event_days($event_id){
        $event = auth()->user()->events()->where('event_id',$event_id)->first();
        return view('admin.event_settings.add_event_days',['event'=>$event]);
    }

    public function store_event_days(Request $request,$event_id){
        $request->validate([
            'date' => 'required|array',
            'date.*' => 'required|date',
            'start_time' => 'required|array',
            'start_time.*' => 'required|date_format:H:i',
            'end_time' => 'required|array',
            'end_time.*' => 'required|date_format:H:i',
        ]);
        $event_days = [];
        foreach($request->date as $key => $date){
            $event_days[] = [
                'date' => $date,
                'start_time' => $request->start_time[$key],
                'end_time' => $request->end_time[$key],
            ];
        }
        $event = auth()->user()->events()->where('event_id',$event_id)->first();
        if(!$event){
            return redirect()->route('admin.events.view');
        }
        $event->event_days()->createMany($event_days);
        return redirect()->route('admin.tickets.add',['event_id'=>$event->id]);
    }

    // Event theme
    public function edit_theme($event_id){
        $event = auth()->user()->events()->where('event_id',$event_id)->first();
        return view('admin.event_settings.event_theme',['event'=>$event]);
    }

    public function store_theme(Request $request,$event_id){
        $request->validate([
            'name' => 'required|string',
            'theme_color' => 'required|string',
            'registration_form_background_color' => 'required|string',
            'registration_page_background_image' => 'nullable|image',
            'registration_page_header_image' => 'nullable|image',
        ]);
        // store images
        if($request->has('registration_page_background_image')){
            $registration_page_background_image = $request->registration_page_background_image->store('public/event_themes');
        }
        if($request->has('registration_page_header_image')){
            $registration_page_header_image = $request->registration_page_header_image->store('public/event_themes');
        }
        
        $event = auth()->user()->events()->where('event_id',$event_id)->first();
        if(!$event){
            return redirect()->route('admin.events.view');
        }
        $theme = $event->themes()->create([
            'name' => $request->name,
            'theme_color' => $request->theme_color,
            'registration_form_background_color' => $request->registration_form_background_color,
            'registration_page_background_image' => $registration_page_background_image ?? null,
            'registration_page_header_image' => $registration_page_header_image ?? null,
            'is_active' => 1,
        ]);
        $event->themes()->where('id','!=',$theme->id)->update(['is_active'=>0]);
        return redirect()->back()->with('success','Theme added successfully!');
    }
}
