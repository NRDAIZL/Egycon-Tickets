<?php

namespace App\Http\Controllers;

use App\Imports\DiscountCodesImport;
use App\Models\TicketDiscountCode;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DiscountCodeController extends Controller
{
    public function index()
    {
        $discount_codes = TicketDiscountCode::with('ticket.post')->paginate(15);
        return view('admin.codes.view', compact('discount_codes'));
    }

    public function create()
    {
        return view('admin.codes.add');
    }

    public function store(Request $request){
        $request->validate([
            'code' => 'required',
        ]);

        $discount_code = new TicketDiscountCode();
        $discount_code->code = $request->code;
        $discount_code->save();
        
        return redirect()->back()->with('success', 'Discount code added successfully');
    }

    public function trash($id){
        $discount_code = TicketDiscountCode::find($id);
        $discount_code->delete();
        return redirect()->back()->with('success', 'Discount code deleted successfully');
    }

    public function restore($id){
        $discount_code = TicketDiscountCode::withTrashed()->find($id);
        $discount_code->restore();
        return redirect()->back()->with('success', 'Discount code restored successfully');
    }


    public function upload(){
        return view('admin.codes.upload');
    }
    public function upload_store(Request $request){
        
        $array = Excel::toArray(DiscountCodesImport::class, $request->file('sheet'));
        $codes = $array[0];
        foreach($codes as $code){
            $discount_code = new TicketDiscountCode();
            $discount_code->code = $code[0];
            $discount_code->save();
        }
        return redirect()->back()->with('success', 'Discount codes uploaded successfully');
    }
}
