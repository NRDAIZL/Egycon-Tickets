<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PostTicket;
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

    public function scan(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'event_id' => 'required'
        ]);

        $user = $request->user();
        $event = $user->events()->where('event_id', $request->event_id)->first();
        if(!$event){
            return response()->json([
                'message' => 'Event not found'
            ], 404);
        }

        $data = PostTicket::with('post', 'ticket_type')->where('code', $request->code)->first();
        // check if data related to event 
        if ($data) {
            $check_event_id = $data->ticket_type->event_id;
            if ($check_event_id != $event->id) {
                return response()->json([
                    'message' => 'Code # ' . $request->code . ' Not Found'
                ], 404);
            }
        }
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Code # ' . $request->code . ' Not Found'
            ], 404);
        } else if (str_contains(strtolower($data->ticket_type->name), 'bus')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Code # ' . $request->code . ' is a Bus Ticket. This page is for event tickets only'
            ], 404);
        } else {
            // return back()->with('message', 'Code # '.$request->code.' Found');
            $status = $data->post->status ?? 1;
            if ($status == 0 || $status == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Code # ' . $request->code . ' is a Bus Ticket. This page is for event tickets only'
                ], 404);
            } else if ($status == 1 && $data->status != 2) {
                $data->status = 2;
                $data->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Scanned Successfully! The registree can enter!, Name:' . ($data->post->name ?? "N/A") . ' Order ID: ' . ($data->post->id ?? "N/A")
                ]);
            } else if ($data->status == 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Already Scanned Before!!!, Name:' . ($data->post->name ?? "N/A") . ' Order ID: ' . ($data->post->id ?? "N/A")
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
