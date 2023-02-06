<?php

namespace App\Http\Controllers;

use App\Models\EventPaymentMethod;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($event_id)
    {
        $payment_methods = EventPaymentMethod::where('event_id', $event_id)->get();
        return view('admin.payment_methods.payment_methods', ['payment_methods' => $payment_methods]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($event_id)
    {
        $payment_methods = PaymentMethod::all();
        return view('admin.payment_methods.add_payment_method', ['payment_methods' => $payment_methods]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$event_id)
    {
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            // 'event_id' => 'required|exists:events,id',
            'account_number' => 'required',
            'account_name' => 'required',
            'name' => 'nullable',
            'description' => 'nullable',
            'is_active' => 'nullable',
        ]);

        $event_payment_method = new EventPaymentMethod();
        $event_payment_method->event_id = $event_id;
        $event_payment_method->payment_method_id = $request->payment_method_id;
        $event_payment_method->account_number = $request->account_number;
        $event_payment_method->account_name = $request->account_name;
        $event_payment_method->name = $request->name;
        $event_payment_method->description = $request->description;
        if($request->has('is_active')){
            $event_payment_method->is_active = 1;
        }else{
            $event_payment_method->is_active = 0;
        }
        $event_payment_method->save();

        return redirect()->back()->with('success', 'Payment method added successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
