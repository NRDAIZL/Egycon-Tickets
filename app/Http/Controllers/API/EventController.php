<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PostTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $events = $user->events()->select('event_id as id', 'name', 'logo')->get();
        $events->map(function($event){
            unset($event->pivot);
            if($event->logo){
                $event->logo = asset(Storage::url($event->logo));
            }
        });
        return response()->json($events);
    }

    public function scan(Request $request, $user = null)
    {
        $request->validate([
            'code' => 'required',
            'event_id' => 'required'
        ]);

   
        $user = $request->user() ?? $user;
        $event = $user->events()->where('event_id', $request->event_id)->first();
        if(!$event){
            return response()->json([
                'message' => 'Event not found'
            ], 404);
        }

        $data = PostTicket::with('post', 'ticket_type.event_days')->where('code', $request->code)->first();
        // check if data related to event 
        if ($data) {

            $ticket_type = $data->ticket_type;
            // check if ticket_type event_day is today
            $today = Carbon::today();
            $event_day = $ticket_type->event_days;
            $event_day = $event_day->where('date', $today->toDateString())->first();
            if (!$event_day) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This ticket is not valid for today'
                ], 404);
            }
            // check if scanned at is today
            if ($data->scanned_at) {
                if( $ticket_type->scan_type == "once" && $data->status == 2){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Already Scanned Before!!! Scanned at: ' . $data->scanned_at,
                    ]);
                }
                $scanned_at = Carbon::parse($data->scanned_at);
                if ($scanned_at->toDateString() == $today->toDateString()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This ticket is already scanned today'
                    ], 404);
                }else{
                    $data->scanned_at = null;
                    $data->status = 1;
                    $data->save();
                }
            }
            $check_event_id = $data->ticket_type->event_id;
            if ($check_event_id != $event->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket Not Found'
                ], 404);
            }
        }
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket Not Found'
            ], 404);
        } else if (str_contains(strtolower($data->ticket_type->name), 'bus')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket is a Bus Ticket. This page is for event tickets only'
            ], 404);
        } else {
            // return back()->with('message', 'Code # '.$request->code.' Found');
            $status = $data->post->status ?? 1;
            if ($status == 0 || $status == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Not accepted yet'
                ], 404);
            } else if ($status == 1 && $data->status != 2) {
                $data->status = 2;
                $data->scanned_at = now();
                $data->save();
                return response()->json([
                    'status' => 'success',
                    'message' => "(Type: {$ticket_type->name}) Scanned Successfully! The registree can enter!, Name:" . ($data->post->name ?? "N/A") . ' Order ID: ' . ($data->post->id ?? "N/A")
                ]);
            } else if ($data->status == 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Already Scanned Before!!! Scanned at: ' . $data->scanned_at,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'There was a problem Scanning! Please refer to the Technical Support Team., Name:' . ($data->post->name ?? "N/A") . ' Order ID: ' . ($data->post->id ?? "N/A")
                ]);
            }
        }

    }
}
