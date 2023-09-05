<?php

namespace App\Http\Controllers;

use App\Models\TicketType;
use Illuminate\Http\Request;
use stdClass;

use function PHPSTORM_META\map;

class TicketController extends Controller
{
    public function view($event_id){
        $ticket_types = auth()->user()->events()->where('event_id',$event_id)->first()->ticket_types()->withTrashed()->paginate(15);
        $posts = auth()->user()->events()->where('event_id', $event_id)->first()->posts()->with('ticket')->get();
        $accepted_tickets_count = [];
        $total_tickets_count = [];
        foreach ($posts as $post) {
                $tickets = $post->ticket;
                foreach ($tickets as $ticket) {
                    $total_tickets_count[$ticket->ticket_type_id] = isset($total_tickets_count[$ticket->ticket_type_id]) ? $total_tickets_count[$ticket->ticket_type_id] + 1 : 1;
                    if($post->status == 1){
                        $accepted_tickets_count[$ticket->ticket_type_id] = isset($accepted_tickets_count[$ticket->ticket_type_id]) ? $accepted_tickets_count[$ticket->ticket_type_id] + 1 : 1;
                    }
            }
        }
        foreach ($ticket_types as $ticket_type) {
            $ticket_type->total_requests = isset($total_tickets_count[$ticket_type->id]) ? $total_tickets_count[$ticket_type->id] : 0;
            $ticket_type->accepted_tickets_count = isset($accepted_tickets_count[$ticket_type->id]) ? $accepted_tickets_count[$ticket_type->id] : 0;
        }
        return view('admin.tickets.view',['ticket_types'=>$ticket_types]);
    }

    public function add($event_id){
        $event_days = auth()->user()->events()->where('event_id',$event_id)->first()->event_days()->get();
        return view('admin.tickets.add',['event_days'=>$event_days]);
    }
    public function edit($event_id,$id){
        $ticket_type = TicketType::with(['event_days'=>function($query){
            return $query->select(['event_day_id as id'])->get();
        }])->find($id);
        $event_days = auth()->user()->events()->where('event_id',$event_id)->first()->event_days()->get();
        return view('admin.tickets.add',['ticket_type'=>$ticket_type,'event_days'=>$event_days]);
    }

    public function store($event_id,Request $request)
    {
        $request->validate([
            'name'=>"required|string",
            "price"=>"required|numeric",
            "persons"=>"required|numeric",
            'type'=>'required|in:qr,discount,noticket,reservation',
            'event_days'=>'required|array',
            'event_days.*'=>'required|numeric',
            'scan_type'=>'required|in:once,once_per_day',
            'is_disabled'=>'required|boolean',
            // 'is_active'=>'required|in:0,1',
            'is_visible'=>'required|boolean',
        ]);
        // check if event day is valid for this event
        $event_days = auth()->user()->events()->where('event_id',$event_id)->first()->event_days()->whereIn('id',$request->event_days)->get();
        if($event_days->count() != count($request->event_days)){
            return redirect()->back()->with('error',"Invalid Event Days!");
        }
        if($request->has('id')){
            $ticket = TicketType::find($request->id);
            $ticket->update([
                'name' => $request->name,
                'price' => $request->price,
                'person' => $request->persons,
                'type' => $request->type,
                'is_active' => true,
                'scan_type' => $request->scan_type,
                'is_disabled' => $request->is_disabled,
                'is_visible' => $request->is_visible,
            ]);
            $ticket->event_days()->sync($request->event_days);
            return redirect()->back()->with('success',"Ticket Type has been updated!");
        }
        $ticket = auth()->user()->events()->where('event_id',$event_id)->first()->ticket_types()->create([
            'name' => $request->name,
            'price' => $request->price,
            'person' => $request->persons,
            'type' => $request->type,
            'is_active' => true,
            'scan_type' => $request->scan_type,
            'is_disabled' => $request->is_disabled,
            'is_visible' => $request->is_visible,
        ]);
        $ticket->event_days()->attach($request->event_days);
        
        return redirect()->back()->with('success',"Ticket Type has been added!");
    }

    public function trash($event_id,$id){
        $ticket_type = TicketType::find($id);
        $ticket_type->delete();
        return redirect()->back()->with('success',"Ticket Type has been deleted!");
    }

    public function restore($event_id,$id){
        $ticket_type = TicketType::withTrashed()->find($id);
        $ticket_type->restore();
        return redirect()->back()->with('success',"Ticket Type has been restored!");
    }
}
