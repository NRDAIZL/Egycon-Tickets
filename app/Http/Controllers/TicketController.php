<?php

namespace App\Http\Controllers;

use App\Models\TicketType;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function view(){
        $ticket_types = TicketType::paginate(15);
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
}
