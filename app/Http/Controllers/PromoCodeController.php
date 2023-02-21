<?php

namespace App\Http\Controllers;

use App\Exports\PromoCodeExport;
use App\Models\PromoCode;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

class PromoCodeController extends Controller
{
    public static function generate_random_string($length = 10)
    {
        $code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
        $code_exists = PromoCode::where('code', $code)->first();
        if ($code_exists) {
            return self::generate_random_string($length);
        }
        return $code;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($event_id)
    {
        $promo_codes = auth()->user()->events()->where('event_id',$event_id)->first()->promo_codes()->with('ticket_types')->paginate(15);
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
            'ticket_type_id' => 'required|array',
            'ticket_type_id.*' => 'required|exists:ticket_types,id',
            'discount' => 'required|numeric|min:0|max:100',
            'max_uses' => 'required|numeric|min:1',
            'is_active' => 'required|boolean',
        ]);
        $event = auth()->user()->events()->where('event_id',$event_id)->first();
        if ($request->has('id')){
            $promo_code = $event->promo_codes()->where('id',$request->id)->first();
            $promo_code->update([
                'code' => $request->code,
                'discount' => $request->discount,
                'max_uses' => $request->max_uses,
                'is_active' => $request->is_active??0,
            ]);
            $promo_code->ticket_types()->sync($request->ticket_type_id);
            return redirect()->route('admin.promo_codes.view',['event_id'=>$event_id])->with('success','Promo code updated successfully!');
        }
        $event->promo_codes()->create([
            'code' => $request->code,
            'discount' => $request->discount,
            'max_uses' => $request->max_uses,
            'is_active' => $request->is_active??0,
            'uses' => 0,
        ]);
        $promo_code = $event->promo_codes()->where('code',$request->code)->first();
        $promo_code->ticket_types()->sync($request->ticket_type_id);

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
        try{
            $promo_code->delete();
        }
        catch (\Exception $e){
            return redirect()->route('admin.promo_codes.view',['event_id'=>$event_id])->with('error','Promo code cannot be deleted after it has been used!');
        }
        return redirect()->route('admin.promo_codes.view',['event_id'=>$event_id])->with('success','Promo code deleted successfully!');
    }

    public function generate($event_id)
    {
        $ticket_types = auth()->user()->events()->where('event_id',$event_id)->first()->ticket_types()->get();
        return view('admin.promo_codes.generate', ['ticket_types'=>$ticket_types]);
    }

    public function generate_store(Request $request, $event_id)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1',
            'ticket_type_id' => 'required',
            'discount' => 'required|numeric|min:0|max:100',
            'max_uses' => 'required|numeric|min:1',
            'is_active' => 'required|boolean',
        ]);
        $event = auth()->user()->events()->where('event_id',$event_id)->first();
        $promo_codes = [];
        for ($i=0; $i < $request->quantity; $i++) { 
            $promo_codes[] = [
                'code' => self::generate_random_string(6),
                'ticket_type_id' => $request->ticket_type_id,
                'discount' => $request->discount,
                'max_uses' => $request->max_uses,
                'is_active' => $request->is_active??0,
                'event_id' => $event_id,
                'uses' => 0,
            ];
        }
        $event->promo_codes()->insert($promo_codes);
        $promo_codes_collections = Collection::make();
        foreach ($promo_codes as $promo_code) {
            $promo_codes_collections->push(PromoCode::where('code',$promo_code['code'])->first());
        }
        $exports = (new PromoCodeExport($event_id, $promo_codes_collections));
        $ticket_type = TicketType::where('id',$request->ticket_type_id)->first();
        $ticket_type_name = preg_replace('/[^A-Za-z0-9\-]/', '', $ticket_type->name);
        $file_name = "promo_codes-{$request->quantity}-{$ticket_type_name}-".date('Y-m-d H:i:s').".xlsx";
        return $exports->download($file_name);
        return redirect()->route('admin.promo_codes.view',['event_id'=>$event_id])->with('success','Promo codes generated successfully!');
    }

    public function export($event_id)
    {
        $event = auth()->user()->events()->where('event_id',$event_id)->first();
        $promo_codes = $event->promo_codes()->get();
        $exports = (new PromoCodeExport($event_id, $promo_codes));
        $file_name = "promo_codes-".date('Y-m-d H:i:s').".xlsx";
        return $exports->download($file_name);
    }

    public function show_requests_for_promo_code($event_id, $promo_code_id)
    {
        $promo_code = auth()->user()->events()->where('event_id', $event_id)->first()->promo_codes()->where('id',$promo_code_id)->first();
        $requests = $promo_code->posts();
        $requests = $requests->paginate(15);

        return view('admin.requests', ['requests'=>$requests,'query'=> "", 'promo_code'=>$promo_code]);
    }
}
