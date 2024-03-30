<?php

namespace App\Http\Livewire\Cards;

use App\Models\Event;
use Livewire\Component;

class TotalRequests extends Component
{

    public $title = 'Requests';
    public $subtitle = '';
    public $icon = 'las la-receipt';
    public $event_id;
    public function mount(){
        $event = app(Event::class);
        $total_requests = $event->getTotalRequests();
        $accepted_requests =
        $event->posts()->where(function ($query) {
            return $query->where('status', 1);
        })->count();
        $pending_requests = $event->posts()->where(function ($query) {
            return $query->where('status', '=', null)->where('picture', '!=', "");
        })->count();
        $this->subtitle = "Total: $total_requests \n Accepted: $accepted_requests \n Pending: $pending_requests";
    }
    public function render()
    {
        return view('livewire.cards.total-requests');
    }
}
