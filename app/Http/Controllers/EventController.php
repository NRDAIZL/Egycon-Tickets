<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\TicketType;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $user_events = auth()->user()->events;
        return view('admin.events.index',['events'=> $user_events]);
    }

    public function add()
    {
        return view('admin.events.add');
    }
}
