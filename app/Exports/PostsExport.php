<?php

namespace App\Exports;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PostsExport implements FromView
{
    // constructor
    private $event_id;
    public function __construct($event_id)
    {
        $this->event_id = $event_id;
    }
   
    public function view(): View
    {
        $event = auth()->user()->events()->where('event_id',$this->event_id)->first();
        $posts = $event->posts()->with(['ticket.ticket_type','ticket_type','provider'])->orderBy('status')->get();
        return view('exports.posts', [
            'requests' => $posts
        ]);
    }
}
