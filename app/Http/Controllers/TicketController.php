<?php

namespace App\Http\Controllers;

use App\Models\TicketType;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function view($event_id){
        $ticket_types = auth()->user()->events()->where('event_id',$event_id)->first()->ticket_types()->paginate(15);
        return view('admin.tickets.view',['ticket_types'=>$ticket_types]);
    }

    public function add($event_id){
        return view('admin.tickets.add');
    }

    public function store($event_id,Request $request)
    {
        $request->validate([
            'name'=>"required|string",
            "price"=>"required|numeric",
            "persons"=>"required|numeric",
            'type'=>'required|in:qr,discount,noticket',
        ]);
        $ticket = auth()->user()->events()->where('event_id',$event_id)->first()->ticket_types()->create([
            'name' => $request->name,
            'price' => $request->price,
            'person' => $request->persons,
            'type' => $request->type,
            'is_active' => true,
        ]);
        
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
