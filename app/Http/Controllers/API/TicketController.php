<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    // search ticket
    public function search(Request $request)
    {
        $request->validate([
            'search_query' => 'required|string',
            'event_id' => 'required|numeric',
        ]);
        // check if the user is the owner of the event
        $event = auth()->user()->events()->where('event_id', $request->event_id)->first();
        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found or you are not allowed to access this event'
            ], 404);
        }

        $event_posts = Post::where('event_id', $request->event_id)->where(function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->search_query . '%')
                ->orWhere('phone_number', 'like', '%' . $request->search_query . '%')
                ->orWhere('email', 'like', '%' . $request->search_query . '%');
        })->with(['ticket'=>function($query){
            return $query->with(['ticket_type'=>function($query){
                return $query->select(['id','price','name'])->get();
            }])->select(['id','post_id','scanned_at','ticket_type_id'])->get();
        }])
        ->select(['id','name','phone_number as phone','email','status'])->get();
        $event_posts->map(function ($post) {
            switch($post->status){
                case null:
                    $post->status = 'pending';
                    break;
                case 0:
                    $post->status = 'rejected';
                    break;
                case 1:
                    $post->status = 'approved';
                    break;
            }
            $post->tickets_count = $post->ticket()->count();
            return $post;
        });
        
        return response()->json($event_posts);
    }
}
