<?php

namespace App\Http\Controllers;

use App\Models\TicketType;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function view(){
        $ticket_types = TicketType::withTrashed()->paginate(15);
        return view('admin.tickets.view',['ticket_types'=>$ticket_types]);
    }

    public function add(){
        return view('admin.tickets.add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'=>"required|string",
            "price"=>"required|numeric",
            "persons"=>"required|numeric"
        ]);
        $ticket_type = new TicketType();
        $ticket_type->name = $request->name;
        $ticket_type->price = $request->price;
        $ticket_type->person = $request->persons;
        $ticket_type->save();
        return redirect()->back()->with('success',"Ticket Type has been added!");
    }

    public function trash($id){
        $ticket_type = TicketType::find($id);
        $ticket_type->delete();
        return redirect()->back()->with('success',"Ticket Type has been deleted!");
    }

    public function restore($id){
        $ticket_type = TicketType::withTrashed()->find($id);
        $ticket_type->restore();
        return redirect()->back()->with('success',"Ticket Type has been restored!");
    }
}
