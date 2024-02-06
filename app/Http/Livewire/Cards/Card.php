<?php

namespace App\Http\Livewire\Cards;

use Livewire\Component;

class Card extends Component
{
    public $title = 'Card Title';
    public $subtitle = 'Card Subtitle';
    public $icon = 'fas fa-user';
    public function render()
    {
        return view('livewire.cards.card');
    }
}
