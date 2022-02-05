<?php

namespace App\View\Components;

use Illuminate\View\Component;

class BelongingsTable extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    protected $belongings;
    public function __construct($belongings)
    {
        $this->belongings = $belongings;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.belongings-table',['belongings'=>$this->belongings]);
    }
}
