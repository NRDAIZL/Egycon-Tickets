<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($event_id)
    {
        $promo_codes = auth()->user()->events()->where('event_id',$event_id)->first()->promo_codes()->paginate(15);
        return view('admin.promo_codes.index',['promo_codes'=>$promo_codes]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($event_id)
    {
        $ticket_types = auth()->user()->events()->where('event_id',$event_id)->first()->ticket_types()->get();
        return view('admin.promo_codes.add',['ticket_types'=>$ticket_types]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $event_id)
    {
        $request->validate([
            'code' => 'required',
            'ticket_type_id' => 'required',
            'discount' => 'required|numeric|min:0|max:100',
            'max_uses' => 'required|numeric|min:1',
            'is_active' => 'required|boolean',
        ]);
        $event = auth()->user()->events()->where('event_id',$event_id)->first();
        if ($request->has('id')){
            $promo_code = $event->promo_codes()->where('id',$request->id)->first();
            $promo_code->update([
                'code' => $request->code,
                'ticket_type_id' => $request->ticket_type_id,
                'discount' => $request->discount,
                'max_uses' => $request->max_uses,
                'is_active' => $request->is_active??0,
            ]);
            return redirect()->route('admin.promo_codes.view',['event_id'=>$event_id])->with('success','Promo code updated successfully!');
        }
        $event->promo_codes()->create([
            'code' => $request->code,
            'ticket_type_id' => $request->ticket_type_id,
            'discount' => $request->discount,
            'max_uses' => $request->max_uses,
            'is_active' => $request->is_active??0,
            'uses' => 0,
        ]);
        return redirect()->route('admin.promo_codes.view',['event_id'=>$event_id])->with('success','Promo code added successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($event_id, $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($event_id, $id)
    {
        $promo_code = auth()->user()->events()->where('event_id', $event_id)->first()->promo_codes()->where('id',$id)->first();
        $ticket_types = auth()->user()->events()->where('event_id',$event_id)->first()->ticket_types()->get();
        return view('admin.promo_codes.add',['promo_code'=>$promo_code,'ticket_types'=>$ticket_types]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$event_id, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($event_id, $id)
    {
        $promo_code = auth()->user()->events()->where('event_id', $event_id)->first()->promo_codes()->where('id',$id)->first();
        $promo_code->delete();
        return redirect()->route('admin.promo_codes.view',['event_id'=>$event_id])->with('success','Promo code deleted successfully!');
    }
}
