<?php

namespace App\Http\Livewire\Cards;

use App\Models\Event;
use Livewire\Component;

class TotalTickets extends Component
{
    public $title = 'Total Accepted Tickets';
    public $subtitle = '';
    public $icon = 'las la-qrcode';
    public $event_id;
    public function mount()
    {
        $event = Event::find($this->event_id);
        $posts = $event->posts()->where('status',1)->get();
        $tickets_count = 0;
        foreach ($posts as $post) {
            $tickets_count += $post->ticket->count();
        }
        $this->subtitle = $tickets_count;
    }

    public function render()
    {
        return view('livewire.cards.total-tickets');
    }
}
