<?php

namespace App\Exports;

use App\Helpers\RequestsHelper;
use App\Models\Post;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PostsExport implements FromView, ShouldAutoSize
{
    // constructor
    private $event_id;
    private $query;
    private $ticket_id;
    public function __construct($event_id, $query = null, $ticket_id = null)
    {
        $this->event_id = $event_id;
        $this->query = $query;
        $this->ticket_id = $ticket_id;
    }

    public function view(): View
    {
        /** @var Event $event */
        $event = auth()->user()->events()->where('event_id',$this->event_id)->first();
        if(empty($this->ticket_id)) {
            $posts = $event->posts();
        }else{
            $posts = $event
                ->ticket_types()
                ->withTrashed()
                ->where('id', $this->ticket_id)
                ->first()
                ->posts();
        }
        /** @var Post $posts */
        $posts = $posts->with([
                    'ticket.ticket_type',
                    'ticket.sub_ticket_type',
                    'ticket_type',
                    'provider',
                    'promo_code'
                ])->orderBy('status', 'asc')->orderBy('created_at','desc');
        if($this->query) {
           $posts = RequestsHelper::searchRequests($posts, $this->query);
        }
        $posts = $posts->get();
        return view('exports.posts', [
            'requests' => $posts
        ]);
    }
}
