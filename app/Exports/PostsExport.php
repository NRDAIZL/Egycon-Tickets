<?php

namespace App\Exports;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PostsExport implements FromView
{
   
    public function view(): View
    {
        return view('exports.posts', [
            'requests' => Post::with(['ticket.ticket_type','ticket_type','provider'])->orderBy('status')->get()
        ]);
    }
}
