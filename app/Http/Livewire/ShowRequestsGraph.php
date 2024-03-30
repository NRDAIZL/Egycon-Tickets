<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\Post;
use Livewire\Component;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class ShowRequestsGraph extends Component
{
    public $event;
    public $data;
    public $event_id;
    private $chart;
    public function mount()
    {
       
        $event = app(Event::class);
        $this->event = $event;

        $posts = Post::with('ticket')->where('event_id', $this->event_id)->where(function ($query) {
            return $query->where('status', '!=', null)->orWhere('picture', '!=', "");
        })->get();
        // get the number of posts for each day
        $this->data = $posts->groupBy(function($date) {
            return \Carbon\Carbon::parse($date->created_at)->format('Y-m-d');
        })->map(function($item, $key) {
            return $item->count();
        })->toArray();

        $this->chart = (new LarapexChart)->lineChart()
            ->setTitle('Requests')
            ->setSubtitle('Requests per day')
            ->addData('Requests', array_values($this->data))
            ->setXAxis(array_keys($this->data))
            ->setColors(['#9333ea']);
            
    }

    

    

    public function render()
    {
        return view('livewire.show-requests-graph', [
            'chart' => $this->chart
        ]);
    }
}
