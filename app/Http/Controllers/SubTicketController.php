<?php

namespace App\Http\Controllers;

use App\Models\SubTicketType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class SubTicketController extends Controller
{
    public function view($event_id){
        $ticket_types = auth()->user()->events()->where('event_id',$event_id)->first()->ticket_types()->paginate(15);
        return view('admin.sub_tickets.view',['ticket_types'=>$ticket_types]);
    }

    public function add($event_id){
        $ticket_types = auth()->user()->events()->where('event_id',$event_id)->first()->ticket_types()->paginate(15);
        return view('admin.sub_tickets.add', ['ticket_types'=>$ticket_types]);
    }

    public function view_posts($event_id, $id){
        $post_tickets = SubTicketType::withTrashed()->find($id)->post_tickets()->groupBy('post_id')->get();
        $posts = new Collection();
        foreach($posts as $post){
            $posts->add($post);
        }
        return view('admin.requests',['requests'=>$posts, 'query'=>false]);
    }

    public function store($event_id,Request $request)
    {
        $request->validate([
            'name'=>"required|string",
            "price"=>"required|numeric",
            "ticket_type_id"=>"required|exists:ticket_types,id",

        ]);
        $ticket = auth()->user()->events()->where('event_id',$event_id)->first()->ticket_types()->where("id",$request->ticket_type_id)->first()->sub_ticket_types()->create([
            'name' => $request->name,
            'price' => $request->price,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success',"Sub Ticket Type has been added!");
    }

    public function trash($event_id,$id){
        $ticket_type = SubTicketType::find($id);
        $ticket_type->delete();
        return redirect()->back()->with('success',"Ticket Type has been deleted!");
    }

    public function restore($event_id,$id){
        $ticket_type = SubTicketType::withTrashed()->find($id);
        $ticket_type->restore();
        return redirect()->back()->with('success',"Ticket Type has been restored!");
    }
}
