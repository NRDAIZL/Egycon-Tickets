<?php

namespace App\Http\Livewire\Cards;

use App\Models\Event;
use Livewire\Component;

class TotalRequests extends Component
{

    public $title = 'Total Requests';
    public $subtitle = '';
    public $icon = 'las la-receipt';
    public $event_id;
    public function mount(){
        $event = Event::find($this->event_id);
        $this->subtitle = $event->posts()->where(function ($query) {
            return $query->where('status', '!=', null)->orWhere('picture', '!=', "");
        })->count();
    }
    public function render()
    {
        return view('livewire.cards.total-requests');
    }
}
