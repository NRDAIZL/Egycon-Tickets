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

        $posts = Post::with('ticket')->where('event_id', $this->event_id)->get();
        // get the number of posts for each day
        $this->data = $posts->groupBy(function ($date) {
            return \Carbon\Carbon::parse($date->created_at)->format('Y-m-d H');
        })->map(function ($item, $key) {
            return $item->count();
        })->toArray();

        // change date format to be more readable
        $this->data = array_map(function ($key, $value) {
            return [$key . ':00', $value];
        }, array_keys($this->data), $this->data);

        $this->data = array_combine(array_column($this->data, 0), array_column($this->data, 1));



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
