<?php

namespace App\Http\Livewire\Cards;

use App\Models\Event;
use Livewire\Component;

class TotalTickets extends Component
{
    public $title = 'Tickets';
    public $subtitle = '';
    public $icon = 'las la-qrcode';
    public $event_id;
    public function mount()
    {
        $event = Event::find($this->event_id);
        $posts = $event->posts()->where('status',1)->get();
        $tickets_count = 0;
        $reservations_count = 0;
        foreach ($posts as $post) {
            foreach($post->ticket as $ticket_type){
                if($ticket_type->ticket_type->type != "reservation"){
                    $tickets_count += 1;
                }
                else{
                    $reservations_count += 1;
                }
            }
        }
        $this->subtitle = "Tickets: $tickets_count \n Reservations: $reservations_count";
    }

    public function render()
    {
        return view('livewire.cards.total-tickets');
    }
}
