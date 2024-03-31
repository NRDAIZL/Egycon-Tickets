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
        /** @var Event $event */
        $event = app(Event::class);
        $posts = $event->posts()->with('ticket.ticket_type')->where('status',1)->get();
        $tickets_count = 0;
        $reservations_count = 0;
        foreach ($posts as $post) {
            foreach($post->ticket as $ticket){
                if($ticket->ticket_type->type != "reservation"){
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
