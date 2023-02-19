<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\Post;
use Livewire\Component;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class ShowScansGraph extends Component
{
    public $event;
    public $data;
    public $event_id;
    private $chart;
    public function mount()
    {

        $event = Event::find($this->event_id);
        $this->event = $event;

        $PostTickets = Post::with(['ticket'=>  function ($query) {
            return $query->where('scanned_at', '!=', null);
        }])->where('event_id', $this->event_id)->get();
        $PostTickets = $PostTickets->map(function ($item, $key) {
            return $item->ticket;
        });
        $PostTickets = $PostTickets->filter(function ($value, $key) {
            return $value != null && $value->contains('scanned_at', '!=', null);
        });

        // change date format to be more readable
        $this->data = $PostTickets->groupBy(function($date) {
            return \Carbon\Carbon::parse($date->first()->scanned_at)->format('Y-m-d H');
        })->map(function($item, $key) {
            return $item->count();
        })->toArray();

        // change the keys to be the dates and the values to be the number of posts
        $this->data = array_combine(array_keys($this->data), array_values($this->data));


        $this->chart = (new LarapexChart)->lineChart()
            ->setTitle('Scans')
            ->setSubtitle('Scans per Hour')
            ->addData('Scans', array_values($this->data))
            ->setXAxis(array_keys($this->data))
            ->setColors(['#9333ea']);
    }





    public function render()
    {
        return view('livewire.show-scans-graph', [
            'chart' => $this->chart
        ]);
    }
}
