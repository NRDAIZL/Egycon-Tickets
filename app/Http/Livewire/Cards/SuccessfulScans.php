<?php

namespace App\Http\Livewire\Cards;

use App\Models\Event;
use Livewire\Component;

class SuccessfulScans extends Component
{
    public $title = 'Successful Scans';
    public $subtitle = '';
    public $icon = 'las la-barcode';
    public $event_id;
    public function mount()
    {
        $event = Event::find($this->event_id);
        $ticket_types = $event->ticket_types()->get();
        $tickets_count = 0;
        foreach ($ticket_types as $ticket_type) {
            $tickets_count += $ticket_type->tickets()->where('status', 1)->count();
        }
        $this->subtitle = $tickets_count;
    }

    public function render()
    {
        return view('livewire.cards.successful-scans');
    }
}
