<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index($event_id){
        $event = Event::find($event_id);
        return view('admin.home', compact('event'));
    }
}
