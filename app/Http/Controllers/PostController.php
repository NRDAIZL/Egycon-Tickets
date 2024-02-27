<?php
namespace App\Http\Controllers;

use DB;
use stdClass;
use Exception;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\Event;
use App\Mail\TicketEmail;
use App\Models\PromoCode;
use App\Models\PostTicket;
use App\Models\TicketType;
use App\Imports\PostImport;
use App\Exports\PostsExport;
use Illuminate\Http\Request;
use Postmark\PostmarkClient;
use App\Models\PaymentMethod;
use App\Models\SubTicketType;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use App\Models\EventEmailTemplate;
use App\Models\EventPaymentMethod;
use App\Models\TicketDiscountCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Postmark\Models\PostmarkException;
use App\Models\ExternalServiceProvider;
use Illuminate\Contracts\Session\Session;
use Nafezly\Payments\Classes\OpayPayment;
use Nafezly\Payments\Classes\KashierPayment;
use App\Http\Controllers\API\EventController;

class PostController extends Controller
{

    public static function generate_random_string($length = 10){
        $code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
        $code_exists = Post::where('code',$code)->first();
        while($code_exists){
            $code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
            $code_exists = Post::where('code',$code)->first();
        }
        return $code;
    }
    public function instructions_code(Request $request, $x_event_id){
        return $this->instructions($request, $x_event_id, true);
    }


    public function instructions(Request $request, $x_event_id, $i_have_a_code = null){
        // check if $x_event_id is slug or id
        if(is_numeric($x_event_id)){
            $event = Event::findOrFail($x_event_id);
            if($event->slug != null){
                if($i_have_a_code)
                    return redirect()->route('promo_code', ['x_event_id' => $event->slug]);
                else
                return redirect()->route('instructions', ['x_event_id' => $event->slug]);
            }
        }else{
            $event = Event::where('slug',$x_event_id)->first();
            if (!$event) {
                return abort(404);
            }
            $x_event_id = $event->id;
        }

        // get event theme
        $theme = $event->themes()->where('is_active',1)->first();
        $ticket_types = $event->ticket_types()->where(['is_active'=>1, 'is_visible'=>1])->orderBy('is_disabled')->get();
        if($ticket_types->count() == 0 ){
            return view('tickets_suspended',['theme' => $theme]);
        }
        if(session()->get('errors')){
            $questions = $event->questions;
            return view('form', ['payment_method' => $request->payment_method, 'ticket_types' => $ticket_types, 'quantity' => old('quantity'), 'total' => old('total'), 'questions' => $questions, 'theme' => $theme, 'event' => $event, 'code' => old('code')]);
        }
        $data = [];
        $data['ticket_types'] = $ticket_types;
        $data['theme'] = $theme;
        $data['event'] = $event;
        if($i_have_a_code){
            $data['i_have_a_code'] = true;
            return view('instructions', $data);
        }
        $payment_methods = EventPaymentMethod::where('event_id', $x_event_id)->where('is_active', true)->get();
        $global_payment_methods = PaymentMethod::all();
        $payment_methods = $payment_methods->map(function ($payment_method) use ($global_payment_methods) {
            $global_payment_method = $global_payment_methods->where('id', $payment_method->payment_method_id)->first();
            if ($payment_method->name == null)
                $payment_method->name = $global_payment_method->name;
            $payment_method->logo = $global_payment_method->logo;
            return $payment_method;
        });
        $data['payment_methods'] = $payment_methods;
        $data['i_have_a_code'] = $i_have_a_code;
        return view('instructions', $data);
    }

    public function instructions_code_store(Request $request, $x_event_id){
        if (is_numeric($x_event_id)) {
            $event = Event::findOrFail($x_event_id);
        } else {
            $event = Event::where('slug', $x_event_id)->first();
            if (!$event) {
                return abort(404);
            }
            $x_event_id = $event->id;
        }
        if ($request->has('name')) {
            return $this->store($request, $x_event_id);
        }
        $request->validate([
            'code' => 'required',
        ]);

        $code = PromoCode::where('code',$request->code)->where('event_id',$x_event_id)->first();
        if(!$code){
            return redirect()->back()->with('status-failure','Invalid code');
        }
        if($code->is_active != 1 || $code->max_uses <= $code->uses){
            return redirect()->back()->with('status-failure','This code has reached the maximum number of uses');
        }
        $ticket_types = $code->ticket_types;
        return redirect()->route('promo_code_tickets',['x_event_id'=>$x_event_id,'code'=>$request->code]);
        // return $this->instructions_store($request, $x_event_id, $code);
    }

    public function instructions_code_show_tickets($x_event_id, $code){
        if (is_numeric($x_event_id)) {
            $event = Event::findOrFail($x_event_id);
        } else {
            $event = Event::where('slug', $x_event_id)->first();
            if (!$event) {
                return abort(404);
            }
            $x_event_id = $event->id;
        }
        $code = PromoCode::where('code',$code)->where('event_id',$x_event_id)->first();
        if(!$code){
            return redirect()->back()->with('status-failure','Invalid code');
        }
        if($code->is_active != 1 || $code->max_uses <= $code->uses){
            return redirect()->back()->with('status-failure','This code has reached the maximum number of uses');
        }
        $ticket_types = $code->ticket_types;
        $ticket_types = $ticket_types->map(function($ticket_type) use ($code){
            $ticket_type->is_disabled = false;
            $ticket_type->price = $ticket_type->price - ($ticket_type->price * ($code->discount/100));
            return $ticket_type;
        });
        $theme = $event->themes()->where('is_active',1)->first();
        $data = [];
        $data['ticket_types'] = $ticket_types;
        $data['theme'] = $theme;
        $data['event'] = $event;
        $payment_methods = EventPaymentMethod::where('event_id',$x_event_id)->where('is_active',true)->get();
        $global_payment_methods = PaymentMethod::all();
        $payment_methods = $payment_methods->map(function($payment_method) use ($global_payment_methods){
            $global_payment_method = $global_payment_methods->where('id',$payment_method->payment_method_id)->first();
            if($payment_method->name == null)
                $payment_method->name = $global_payment_method->name;
            $payment_method->logo = $global_payment_method->logo;
            return $payment_method;
        });
        $data['payment_methods'] = $payment_methods;
        $data['i_have_a_code'] = false;
        $data['code'] = $code;
        if($code->discount > 0)
            $data['discount'] = $code->discount;
        return view('instructions', $data);
    }
    public function instructions_code_show_tickets_store(Request $request, $x_event_id){
        return $this->instructions_store($request, $x_event_id);
    }

    public function instructions_store(Request $request, $x_event_id, $code = null)
    {

        // check if $x_event_id is slug or id
        if (is_numeric($x_event_id)) {
            $event = Event::findOrFail($x_event_id);
        } else {
            $event = Event::where('slug', $x_event_id)->first();
            if (!$event) {
                return abort(404);
            }
            $x_event_id = $event->id;
        }

        if ($request->has('name')) {
            return $this->store($request, $x_event_id);
        }

        $event = Event::findOrFail($x_event_id);
        $ticket_types = $event->ticket_types()->where(['is_visible'=> 1])->orderBy('is_disabled')->get();

        $theme = $event->themes()->where('is_active', 1)->first();
        $questions = $event->questions;
        $promo_code = null;
        if($request->has('promo_code')){
            $code = PromoCode::where('code',$request->promo_code)->where('event_id',$x_event_id)->first();
            if(!$code){
            return redirect()->back()->with('status-failure','Invalid code');
            }
            if($code->is_active != 1 || $code->max_uses <= $code->uses){
                return redirect()->back()->with('status-failure','This code has reached the maximum number of uses');
            }
            $promo_code = $code;
            $ticket_types = $code->ticket_types;
        }
        $request->validate([
            'quantity' => 'array',
            'quantity.*' => 'numeric|min:0|max:10',
        ]);
        $total = 0;

        $i = 0;
        // check if any of selected tickets where quantity > 0 is a reservation ticket
        $total_reservation_ticket = 0;
        foreach ($ticket_types as $ticket_type) {
            if($ticket_type->type == "reservation" && $request->quantity[$i] > 0){
                $total_reservation_ticket += $request->quantity[$i];
            }
            $i++;
        }

        if($total_reservation_ticket > 5){
            return redirect()->back()->with('status-failure', 'You cannot select more than 5 reservation tickets');
        }

        $request_include_reservations = $total_reservation_ticket > 0;
        $i = 0;
        foreach ($ticket_types as $ticket_type) {
            // check if request includes not reservation tickets and reservation tickets at the same time and return error
            if($request_include_reservations && $ticket_type->type != "reservation" && $request->quantity[$i] > 0){
                return redirect()->back()->with('status-failure', 'You cannot select reservation tickets with other tickets');
            }
            $price = $ticket_type->price * $request->quantity[$i];
            $total += $price;
            $i++;
        }
        if($request->has('promo_code')){
            $total = $total - ($total*($promo_code->discount/100));
        }
        if ($total == 0 && !$request_include_reservations && !$request->has('promo_code')) {
            return redirect()->back()->with('status-failure', 'You must select at least on ticket');
        }

        $payment_method = $request->payment_method;
        if (!$payment_method && !$request_include_reservations && $total > 0) {
            return redirect()->back()->with('status-failure', 'Please select a payment method');
        }
        $payment_method = $request_include_reservations ? "reservation" : $payment_method;
        return view('form', ['reservation'=> $request_include_reservations, 'payment_method' => $payment_method, 'ticket_types' => $ticket_types, 'total' => $total, 'quantity' => $request->quantity, 'theme' => $theme, 'event' => $event, 'questions' => $questions, 'code' => $code]);
    }

    public function delete_all_view(){
        $random_string = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4);
        return view('admin.reset-tickets',['random_string'=>$random_string]);
    }

    public function delete_all(Request $request, $event_id){
        if(strtoupper($request->random_string) != strtoupper($request->random_string_confirm)){
            return redirect()->back()->with('status-failure','The text does not match. Please try again');
        }
        $posts = Event::find($event_id)->posts;
        $post_tickets = PostTicket::whereIn('post_id',$posts->pluck('id'))->get();
        $post_tickets->each(function($post_ticket){
            $post_ticket->delete();
        });
        $posts->each(function($post){
            $post->delete();
        });
        return redirect()->route('admin.home',$event_id)->with('success','All tickets have been deleted');
    }


    public function edit_requests()
    {
        return view('admin.edit-requests');
    }

    public function action(Request $request, $event_id)
    {
        $request->validate([
            'code' => 'required',
        ]);
        $form_request = new Request(['event_id' => $event_id, 'code' => $request->code]);
        $controller = new EventController();
        $response = $controller->scan($form_request,Auth::user());
        $response = json_decode($response->getContent());
        return back()->with($response->status, $response->message);
        // $data = PostTicket::with('post','ticket_type')->where('code',$request->code)->first();
        // // check if data related to event
        // if($data){
        //     $check_event_id = $data->ticket_type->event_id;
        //     if($check_event_id != $event_id){
        //         return back()->with('error', 'Code # '.$request->code.' Not Found');
        //     }
        // }
        // if(!$data){
        //     return back()->with('error', 'Code # '.$request->code.' Not Found');
        // }else if(str_contains(strtolower($data->ticket_type->name),'bus')){
        //     return back()->with('error', 'Code # ' . $request->code . ' is a Bus Ticket. This page is for event tickets only');
        // }else{
        //     // return back()->with('message', 'Code # '.$request->code.' Found');
        //     $status = $data->post->status??1;
        //     if($status == 0 || $status == null){
        //         return back()->with('error', 'Not accepted yet');
        //     }else if($status == 1 && $data->status != 2){
        //         $data->status = 2;
        //         $data->scanned_at = now();
        //         $data->save();
        //             return back()->with('message', 'Scanned Successfully! The registree can enter!, Name:'.($data->post->name??"N/A").' Order ID: '. ($data->post->id??"N/A"));
        //     }else if($data->status == 2){
        //         return back()->with('error', 'Already Scanned Before!!!, Name:'.($data->post->name??"N/A").' Order ID: '. ($data->post->id??"N/A"));
        //     }else{
        //         return back()->with('error', 'There was a problem Scanning! Please refer to the Technical Support Team., Name:'.($data->post->name??"N/A").' Order ID: '. ($data->post->id??"N/A"));
        //     }
        // }

    }
    private function generate_post_ticket(&$post, $ticket, $theme, $event, $sub_ticket_type_id){
        $unique_id = $this->generate_random_string(6);
        $post_ticket = new PostTicket();
        $post_ticket->post_id = $post->id;
        $post_ticket->ticket_type_id = $ticket->id;
        if($sub_ticket_type_id != null){
            $post_ticket->sub_ticket_type_id = $sub_ticket_type_id;
        }
        if ($ticket->type == "qr") {
            $post_ticket->code = $unique_id;
            $qr_options = new QROptions([
                'version'    => 5,
                'outputType' => QRCode::OUTPUT_IMAGE_JPG,
                'eccLevel'   => QRCode::ECC_L,
                'imageTransparent' => false,
                'imagickFormat' => 'jpg',
                'imageTransparencyBG' => [255, 255, 255],
            ]);
            $qrcode = new QRCode($qr_options);
            if(false)
            $qrcode->render($unique_id, public_path('images/qrcodes/' . $unique_id . ".jpg"));
        } else if ($ticket->type == "discount") {
            $discount_ticket = TicketDiscountCode::where('claimed_at', null)->first();
            if ($discount_ticket) {
                $post_ticket->discount_code_id = $discount_ticket->id;
                $discount_ticket->claimed_at = Carbon::now();
                $discount_ticket->save();
            } else {
                $post->delete();
                return redirect()->back()->with(["status-failure" => "An error occurred while processing your request. Please try again later. Error code: 1", 'theme' => $theme, 'event' => $event]);
            }
        } else if($ticket->type == "reservation" || $ticket->type == "noticket"){
            $post_ticket->code = null;
        } else {
            $post->delete();
            return redirect()->back()->with(["status-failure" => "An error occurred while processing your request. Please try again later. Error code: 2", 'theme' => $theme, 'event' => $event]);
        }
        $post_ticket->save();
    }
    public function store(Request $request, $x_event_id)
    {
        $request->validate([
            'total'=>"numeric",
            'name'=>"required|string|min:6|max:64",
            'email' => "required|email",
            'phone_number' => "required",
            'promo_code' => 'nullable|exists:promo_codes,code',
            // 'ticket_type_id' => 'required_with:promo_code|exists:ticket_types,id',
            'unique_code' => 'required|unique:posts,code',
            'quantity' => 'array|required',
            'quantity.*' => 'numeric|min:0|max:10',
        ],[
            'unique_code.unique' => 'You have already registered, if you have any questions please contact us.',
        ]);

        if($request->total > 0){
            $request->validate(['payment_method' => "required",

            ]);
        }

        // check if promo code is valid on the selected ticket type
        if ($request->promo_code) {
            $promo = PromoCode::where('code', $request->promo_code)
            ->where('is_active', 1)
            ->where('max_uses', '>', 'uses')
            ->where('event_id',$x_event_id)
            ->first();
            if(!$promo){
                session()->flash('status-failure', 'Promo code is not valid.');
                session()->flashInput($request->input());
                return redirect()->route('promo_code_tickets', ['event_id' => $x_event_id, 'code' => $request->promo_code]);
            }
            if ($promo->ticket_type_id != $request->ticket_type_id) {
                session()->flash('status-failure', 'Promo code is not valid.');
                session()->flashInput($request->input());
                return redirect()->route('promo_code_tickets', ['event_id' => $x_event_id, 'code' => $request->promo_code]);
            }

        }
        if ($request->payment_method == "vodafone_cash" && $request->total > 0) {
            $request->validate([
                'receipt' => "required|file|mimes:png,jpg,jpeg",
            ]);
        }
        $event = Event::findOrFail($x_event_id);
        $questions  = $event->questions;
        $ticket_types = [];
        $theme = $event->themes()->where('is_active', 1)->first();
        if($request->promo_code){
            $promo = PromoCode::where('event_id',$x_event_id)->where('code', $request->promo_code)->where('is_active', 1)->where('max_uses', '>', 'uses')->first();
            if(!$promo){
                session()->flash('status-failure', 'Promo code is not valid.');
                session()->flashInput($request->input());
                return redirect()->route('promo_code_tickets', ['event_id' => $x_event_id, 'code' => $request->promo_code]);
            }
            $ticket_types = $promo->ticket_types;
        }else{
            // $ticket_types = $event->ticket_types;
            $ticket_types = TicketType::where('event_id',$x_event_id)->where(['is_active' => 1, 'is_visible' => 1])->orderBy('is_disabled')->get();
        }
        if (strpos(trim($request->name), ' ') === false) {
            session()->flash('status-failure', 'Please enter your full name.');
            session()->flashInput($request->input());
            return view('form', ['ticket_types' => $ticket_types, 'theme'=>$theme, 'total' => $request->total, 'quantity' => $request->quantity, 'payment_method'=>$request->payment_method, 'questions' => $questions, 'code' => $request->promo_code, 'event' => $event]);
        }

        // get submited question answers and store them in an array
        $answers = [];
        foreach($questions as $question){
            $answers[$question->question] = $request->input('question_'.$question->id);
        }



        $post = new Post;
        $post->payment_method = $request->payment_method;
        $post->event_id = $x_event_id;
        // check that the selected file is image and save it to a folder
        // $post->receipt = $request->receipt;
        if($request->hasFile('receipt')){
            $image = $request->file('receipt');
            $image_name = time().'-'.$image->getClientOriginalName();
            $image->move(public_path('/images'), $image_name);
            $post->picture = $image_name;
        }
        $post->name = $request->name;
        $post->email = $request->email;
        $post->ticket_type_id = $request->ticket_type_id;
        $post->order_reference_id = uniqid();
        $post->phone_number = $request->phone_number;
        if(count($answers) > 0){
            $post->answers = json_encode($answers);
        }
        if(preg_match('@[0-9]@', $post->phone_number) == 0 ){
            return redirect()->back()->with('status-failure', 'Phone number must be numbers only!');
        }
        $post->code = $request->unique_code;
        $total_price = 0;
        $tickets = $ticket_types;
        $total_quantity = array_sum($request->quantity);

        $i = 0;
        // check if any of selected tickets where quantity > 0 is a reservation ticket
        $total_reservation_ticket = 0;
        $selected_sub_ticket_types = [];
        foreach ($ticket_types as $ticket_type) {
            if ($ticket_type->type == "reservation" && $request->quantity[$i] > 0) {
                $total_reservation_ticket += $request->quantity[$i];
            }
            if($ticket_type->sub_ticket_types()->count() > 0 && $request->quantity[$i] > 0){
                $selected_sub_ticket_types[$ticket_type->id] = [];
                foreach($request["sub_ticket_".$ticket_type->id] as $sub_ticket_type){
                    $sub_ticket_type = SubTicketType::find($sub_ticket_type);
                    if($sub_ticket_type == null || empty($sub_ticket_type)){
                        return redirect()->back()->with('status-failure', 'Invalid sub ticket selection');
                    }
                    for($j = 0; $j < $ticket_type->person; $j++){
                        $selected_sub_ticket_types[$ticket_type->id][] = $sub_ticket_type->id;
                    }
                }
            }
            $i++;
        }
        if ($total_reservation_ticket > 5) {
            return redirect()->back()->with('status-failure', 'You cannot select more than 5 reservation tickets');
        }
        $request_include_reservations = $total_reservation_ticket > 0;

        // check if the same email has already created a reservation ticket in the same event
        $posts = Post::where('email', $request->email)->where('event_id', $x_event_id)->get();
        foreach ($posts as $P) {
            $post_tickets = $P->ticket;
            foreach ($post_tickets as $post_ticket) {
                if ($post_ticket->ticket_type->type == "reservation" && $request_include_reservations) {
                    return redirect()->back()->with('status-failure', 'Failed to register your request. Maximum number of reservations is: 1 per email.');
                }
            }
        }

        $j = 0;
        foreach($request->quantity as $quantity){

            $ticket = $tickets[$j];
            if ($request_include_reservations && $ticket->type != "reservation" && $quantity > 0) {
                return redirect()->back()->with('status-failure', 'You cannot select reservation tickets with other tickets');
            }

            if(isset($promo)){
                if($total_quantity > $promo->max_uses - $promo->uses && $quantity != 0){
                    return redirect()->back()->with('status-failure', 'Number of tickets exceeds limit!');
                }
                $total_price += ($ticket->price - (($promo->discount / 100) * $ticket->price))*$quantity;
                $post->promo_code_id = $promo->id;
                $promo->uses = $promo->uses + $quantity;
                if ($promo->uses >= $promo->max_uses) {
                    $promo->is_active = 0;
                }

            } else {
                $total_price += $quantity * $ticket->price;
            }
            for($i = 0; $i<$quantity*$ticket->person; $i++){
                $post->save();
                $sub_ticket_type = null;
                if(isset($selected_sub_ticket_types[$ticket->id])){
                    $sub_ticket_type = $selected_sub_ticket_types[$ticket->id][$i];
                }
                $response = $this->generate_post_ticket($post, $ticket, $theme, $event, $sub_ticket_type);
                if($response){
                    return $response;
                }
            }

            $j++;
        }
        if ($total_price > $request->total) {
            $post->delete();
            return redirect()->back()->with('status-failure', 'There has been an issue with your request!');
        }
        $post->total_price = $total_price;

        if($request->payment_method == "reservation" && $total_quantity == $total_reservation_ticket){
           return $this->accept($x_event_id, $post->id, true);
        }
        $post->save();
        dd($post);
        if(isset($promo)){
            $promo->save();
        }
        if ($total_price == 0 && isset($promo)) {
            $post->status = 1;
            $post->save();

            $this->accept($x_event_id, $post->id, true);
            return redirect()->route('thank_you', [
                'x_event_id' => $x_event_id,
            ]);

        }

        if ($request->payment_method == "vodafone_cash" && $total_price > 0) {
            $request->validate([
                'receipt' => "required|file|mimes:png,jpg,jpeg",
            ]);
        }

        $post->save();
        // OPAY
        // if($request->payment_method == "credit_card"){
        //     // calculate the total amount
        //     $total = 0;
        //     $j=0;
        //     foreach($request->quantity as $quantity){
        //         $ticket = $tickets[$j];
        //         $total += $quantity*$ticket->price;
        //         $j++;
        //     }
        //     $data = [
        //         "amount" => $total,
        //         "user_first_name" => explode(' ', $request->name)[0],
        //         "user_last_name" => explode(' ', $request->name)[1],
        //         "user_email" => $request->email,
        //         "user_phone" => $request->phone_number,
        //         "order_id" => $post->id,
        //     ];
        //     return $this->online_payment($data);
        // }
        // KASHIER
        if($request->payment_method == "credit_card" && $total_price > 0 ){
            $post->save();
            // calculate the total amount
            $total = $post->total_price;
            $j=0;
            $data = new \stdClass();
            $data->amount = $total;
            $data->user_first_name = explode(' ', $request->name)[0];
            $data->user_last_name = explode(' ', $request->name)[1];
            $data->user_email = $request->email;
            $data->user_phone = $request->phone_number;
            $data->order_id = $post->id;
            $data->currency = "EGP";
            $data->order_reference_id = $post->order_reference_id;
            $data->event_id = $x_event_id;
            $payment_methods = PaymentMethod::where('name','Kashier')->first();
            if(!$payment_methods){
                $post->delete();

                return redirect()->back()->with(["status-failure" =>"An error occurred while processing your request. Please try again later. Error code: 3", 'theme' => $theme, 'event' => $event]);
            }
            $event_payment_method = EventPaymentMethod::where('event_id',$x_event_id)->where('payment_method_id',$payment_methods->id)->first();
            if(!$event_payment_method){
                $post->delete();

                return redirect()->back()->with(["status-failure" =>"An error occurred while processing your request. Please try again later. Error code: 4", 'theme' => $theme, 'event' => $event]);
            }
            if(isset($promo))
                $promo->save();
            return view('kashier', ['data' =>$data, 'theme' => $theme, 'event' => $event, 'event_payment_method' => $event_payment_method]);
        }
        if(isset($promo))
            $promo->save();
        return redirect()->route('thank_you', ['x_event_id' => $x_event_id]);
    }

    public function thank_you($x_event_id){
        $event = Event::findOrFail($x_event_id);
        $theme = $event->themes()->where('is_active', 1)->first();

        return view('thank_you', ['status_success' => 'Thank you for registering at EGYcon. An email will be sent to you once your request is reviewed.', 'theme' => $theme, 'event' => $event]);
    }

    private function send_email($ticket,$request, $email_template = null){
        if(str_contains(strtolower($ticket->ticket_type->type),'noticket')){
            return;
        }
        if (str_contains(strtolower($ticket->ticket_type->type), 'discount')) {
            return;
        }
        try {
            $client = new PostmarkClient(env("POSTMARK_TOKEN"));
            $data = [
                "name" => explode(' ', $request->name)[0],
                "ticket_type" => $ticket->ticket_type->name,
                "order_id" => $request->id,
                // "date"=>date('Y/m/d'),

            ];
            $template_id = 0;
            if($ticket->code != null){
                $data["qrcode"] = asset('images/qrcodes/' . $ticket->code . '.jpg');
                $data["code"] = $ticket->code;
                $template_id = 27131977;
            } else if ($ticket->discount_code_id != null) {
                $data["code"] = $ticket->discount_code->code;
                $template_id = 29184862; //TODO:Add new template id
            }

            if($email_template != null){
                $body = $email_template->body;
                $subject = $email_template->subject;
                foreach($data as $key => $value){
                    $body = str_replace("{{" . $key . "}}", $value, $body);
                    $subject = str_replace("{{" . $key . "}}", $value, $subject);
                }
                $message = [
                    'To' => $request->email,
                    'From' => "egycon@gamerslegacy.net",
                    'TrackOpens' => true,
                    'Subject' => $subject,
                    'HtmlBody' => $body,
                    'Tag' => $request->event->name,
                    'MessageStream' => "outbound" // here you can set your custom Message Stream
                ];

                $sendResult = $client->sendEmailBatch([$message]);
            }

            // $sendResult = $client->sendEmailWithTemplate(
            //     "egycon@gamerslegacy.net",
            //     $request->email,
            //     $template_id,
            //     $data
            // );

        } catch (PostmarkException $ex) {
            // dd($ex);
            // If the client is able to communicate with the API in a timely fashion,
            // but the message data is invalid, or there's a server error,
            // a PostmarkException can be thrown.
            echo $ex->httpStatusCode;
            echo $ex->message;
            echo $ex->postmarkApiErrorCode;
        } catch (Exception $generalException) {
            dd($generalException);
            // A general exception is thrown if the API
            // was unreachable or times out.
        }
    }

    public function view_requests(Request $request,$event_id){
        $q = false;
         $statusPriorities = [1, 0];

        $evnt = Event::find($event_id);
        if($evnt == null){
            return redirect()->back()->with(["status-failure" => "An error occurred while processing your request. Please try again later. Error code: 3"]);
        }
        $posts = $evnt->posts()->with(['ticket.ticket_type','ticket_type','provider'])->orderByRaw('FIELD (status, ' . implode(', ', $statusPriorities) . ') ASC');
        if($request->has('q')){
            $q = $request->q;
            $posts = $posts->where(function($query) use ($q){
                return $query
                ->orWhere('email', 'like', '%' . $q . '%')
                ->orWhere('phone_number', 'like', '%' . $q . '%')
                ->orWhere('id', 'like', '%' . $q . '%')
                ->orWhere('order_reference_id', 'like', '%' . $q . '%')
                ->orWhere('name', 'like', '%' . $q . '%');
            });
        }else{
            $posts = $posts->where('event_id',$event_id)->orderBy('created_at',"DESC");
        }
        // filter if status is null and no reciept
        $posts = $posts->where(function($query){
                return $query->where('status','!=', null)->orWhere('picture', '!=', "")->orWhere(function ($q) {
                    return $q->where('picture', null)->orWhere('payment_method', 'reservation');
                });
        });

        if($request->has('q')) {
            $posts = $posts->paginate(1000);
        }else{
            $posts = $posts->paginate(15);
        }
        return view('admin.requests',['requests'=>$posts, 'query'=>$q]);
    }

    public function view_ticket_type_requests(Request $request, $event_id, $ticket_type_id)
    {
        $q = false;
        $statusPriorities = [1, 0];

        $evnt = Event::find($event_id);
        if ($evnt == null) {
            return redirect()->back()->with(["status-failure" => "An error occurred while processing your request. Please try again later. Error code: 3"]);
        }
        $ticket_type = TicketType::where('id', $ticket_type_id)->where('event_id', $event_id)->withTrashed()->first();
        $posts = $ticket_type->posts()->with(['ticket.ticket_type', 'ticket_type', 'provider'])->orderBy('status', 'DESC')->orderBy('created_at', "DESC");
        if ($request->has('q')) {
            $q = $request->q;
            $posts = $posts->where(function ($query) use ($q) {
                return $query
                    ->orWhere('email', 'like', '%' . $q . '%')
                    ->orWhere('phone_number', 'like', '%' . $q . '%')
                    ->orWhere('posts.id', 'like', '%' . $q . '%')
                    ->orWhere('order_reference_id', 'like', '%' . $q . '%')
                    ->orWhere('name', 'like', '%' . $q . '%');
            });
        } else {
            $posts = $posts->where('event_id', $event_id)->orderBy('created_at', "DESC");
        }
        // filter if status is null and no reciept
        $posts = $posts->where(function ($query) {
            return $query->where('posts.status', '!=', null)->orWhere('picture', '!=', "");
        });

        if ($request->has('q')) {
            $posts = $posts->paginate(1000);
        } else {
            $posts = $posts->paginate(15);
        }
        return view('admin.requests', ['requests' => $posts, 'query' => $q]);
    }

    public function accept($event_id = null,$id, $through_payment = false){
        $post = Post::with('ticket.ticket_type','event')->findOrFail($id);
        if(!$through_payment){
            $user_events = auth()->user()->events()->pluck('event_id')->toArray();
            // check if post event is in user events
            if (!in_array($post->event_id, $user_events) || $event_id != $post->event_id) {
                return redirect()->back()->with(["status-failure" => "You are not allowed to view this page!"]);
            }
        }
        foreach($post->ticket as $ticket){

            // check if ticket is a BUS ticket (no email will be sent)
            if (str_contains(strtolower($ticket->ticket_type->name), 'bus')) {
                $ticket->ticket_type->type = "reservation";
            }
            if($ticket->ticket_type->type == "reservation"){
                $email_template = EventEmailTemplate::where('event_id', $post->event_id)->where('type', 'reservation')->first();
            }else{
                $email_template = EventEmailTemplate::where('event_id', $post->event_id)->where('type', 'approved')->first();
            }
            if(!$email_template){
                $email_template = new stdClass();
                if($ticket->ticket_type->type == "reservation"){
                    // get html value from assets
                    $body = file_get_contents(public_path('emails/reservation.html'));
                    $subject = "Your request has been approved!";
                    $email_template->type = 'reservation';
                }else{
                    $body = file_get_contents(public_path('emails/approved.html'));
                    $subject = "Your ticket has been approved!";
                    $email_template->type = 'approved';
                }
                $email_template->event_id = $post->event_id;
                $email_template->body = $body;
                $email_template->subject = $subject;
                dd($email_template);
            }
            $this->send_email($ticket,$post, $email_template);
        }
        $post->status = 1;
        $post->save();
        return redirect()->back()->with(["success"=>"{$post->name}'s request has been accepted successfully!"]);
    }

    public function view_tickets($event_id,$id){
        $post = Post::with('ticket.ticket_type','ticket.sub_ticket_type')->findOrFail($id);
        // get all event ids through ticket_types
        $event_ids = $post->ticket_type->pluck('event_id')->toArray();
        // get all events that user has access to
        $user_events = auth()->user()->events()->pluck('event_id')->toArray();
        // check if all event ids are in user events
        if (!empty(array_diff($event_ids, $user_events))) {
            return redirect()->back()->with(["status-failure" => "You are not allowed to view this page!"]);
        }
        return view('admin.view_tickets',['post'=>$post]);
    }

    public function scan_ticket($event_id,$id,$ticket_id){
        $ticket = PostTicket::where('id', $ticket_id)->where('post_id',$id)->first();
        if(!$ticket){
            return redirect()->back();
        }
        $ticket->scanned_at = date("Y-m-d H:i:s");
        $ticket->save();
        return redirect()->back();
    }

    private function send_declined_email($request, $email_template = null)
    {

        try {
            $client = new PostmarkClient(env("POSTMARK_TOKEN"));
            if ($email_template != null) {
                $body = $email_template->body;
                $subject = $email_template->subject;
                $data = [
                    'name' => $request->name,
                    'event_name' => $request->event->name,
                    'order_id' => $request->id,
                ];
                foreach ($data as $key => $value) {
                    $body = str_replace("{{" . $key . "}}", $value, $body);
                    $subject = str_replace("{{" . $key . "}}", $value, $subject);
                }
                $message = [
                    'To' => $request->email,
                    'From' => "egycon@gamerslegacy.net",
                    'TrackOpens' => true,
                    'Subject' => $subject,
                    'HtmlBody' => $body,
                    'Tag' => $request->event->name,
                    'Headers' => ["X-CUSTOM-HEADER" => "Header content"],
                    'MessageStream' => "outbound" // here you can set your custom Message Stream
                ];

                $sendResult = $client->sendEmailBatch([$message]);
            }
            // $sendResult = $client->sendEmailWithTemplate(
            //     "egycon@gamerslegacy.net",
            //     $request->email,
            //     27132435,
            //     [
            //         "name" => explode(' ', $request->name)[0],
            //         "order_id" => $request->id,
            //     ]
            // );

            // Getting the MessageID from the response
            // echo $sendResult->MessageID;
        } catch (PostmarkException $ex) {
            dd($ex);
            // If the client is able to communicate with the API in a timely fashion,
            // but the message data is invalid, or there's a server error,
            // a PostmarkException can be thrown.
            echo $ex->httpStatusCode;
            echo $ex->message;
            echo $ex->postmarkApiErrorCode;
        } catch (Exception $generalException) {
            dd($generalException);
            // A general exception is thrown if the API
            // was unreachable or times out.
        }
    }
    public function reject($event_id,$id, $through_payment = false)
    {
        $post = Post::with('ticket.ticket_type','event')->findOrFail($id);
        if (!$through_payment) {
            $user_events = auth()->user()->events()->pluck('event_id')->toArray();
            // check if post event is in user events
            if (!in_array($post->event_id, $user_events) || $event_id != $post->event_id) {
                return redirect()->back()->with(["status-failure" => "You are not allowed to view this page!"]);
            }
        }
        $email_template = EventEmailTemplate::where('event_id', $post->event_id)->where('type', 'declined')->first();
        if (!$email_template) {
            // get html value from assets
            $body = file_get_contents(public_path('emails/declined.html'));
            $subject = "Your request has been declined";
            $email_template = new stdClass();
            $email_template->body = $body;
            $email_template->subject = $subject;
        }
        $this->send_declined_email($post, $email_template);
        $post->status = 0;
        $post->save();
        return redirect()->back()->with(["success" => "{$post->name}'s request has been rejected successfully!"]);
    }
    public function destroy($event_id,$id)
    {
        $post = Post::with('ticket.ticket_type','event')->findOrFail($id);
        $user_events = auth()->user()->events()->pluck('event_id')->toArray();
        // check if post event is in user events
        if (!in_array($post->event_id, $user_events)) {
            return redirect()->back()->with(["status-failure" => "You are not allowed to view this page!"]);
        }
        foreach($post->ticket as $ticket){
            $ticket->delete();
        }
        $post->delete();
        return redirect()->back()->with(["success" => "{$post->name}'s request has been deleted successfully!"]);
    }
    public function import_sheet(){
        $providers = ExternalServiceProvider::all();
        return view('admin.import',['providers'=>$providers]);
    }

    public function import_sheet_store(Request $request,$event_id)
    {
        $request->validate([
            'sheet'=>'required|file',
            'provider_id'=>'required|exists:external_service_providers,id'
        ]);
        $array = Excel::toArray(PostImport::class,$request->file('sheet'));

        foreach($array[0] as $k=>$row){
            if($row[1]==null||$k == 0 || Post::where('external_service_provider_order_id',intval($row[0]))->where('external_service_provider_id',$request->provider_id)->first()){
                continue;
            }
            $post = new Post();
            $post->external_service_provider_id = $request->provider_id;
            $post->external_service_provider_order_id = intval($row[0]);
            $post->name = $row[1];
            $post->phone_number = $row[2];
            $post->email = $row[3];
            $post->external_service_provider_payment_method = $row[6];
            $post->external_service_provider_notes = $row[7];

            $quantity = $row[4];
            $ticket_type = TicketType::where('name',$row[5])->where('event_id',$event_id)->first();
            if(!$ticket_type){
                continue;
            }
            $unique_id = uniqid();
            $post->code = $unique_id;
            $post->picture = 0;
            $post->status=1;
            $post->save();
            for ($i = 0; $i < $quantity * $ticket_type->person; $i++) {
                $unique_id = $this->generate_random_string(6);
                $post_ticket = new PostTicket();
                $post_ticket->post_id = $post->id;
                $post_ticket->ticket_type_id = $ticket_type->id;
                if(config('settings.enable_qr')){
                    $post_ticket->code = $unique_id;
                    $qr_options = new QROptions([
                        'version'    => 5,
                        'outputType' => QRCode::OUTPUT_IMAGE_JPG,
                        'eccLevel'   => QRCode::ECC_L,
                        'imageTransparent' => false,
                        'imagickFormat' => 'jpg',
                        'imageTransparencyBG' => [255, 255, 255],
                    ]);
                    $qrcode = new QRCode($qr_options);
                    $qrcode->render($unique_id, public_path('images/qrcodes/' . $unique_id . ".jpg"));
                }else if(config('settings.enable_codes')){
                    $discount_ticket = TicketDiscountCode::where('claimed_at',null)->first();
                    if($discount_ticket){
                        $post_ticket->discount_code_id = $discount_ticket->id;
                        $discount_ticket->claimed_at = Carbon::now();
                        $discount_ticket->save();
                    }else{
                        $post->delete();
                        return redirect()->back()->with(["status-failure"=>"There are no discount tickets left!"]);
                    }
                }else{
                    $post->delete();
                    return redirect()->back()->with(["status-failure" => "There are no settings enabled for ticket codes!"]);
                }
                $post_ticket->save();
                $post_ticket = PostTicket::with('ticket_type')->find($post_ticket->id);
                $this->send_email($post_ticket, $post);
            }
        }
        return redirect()->back()->with('success',"Sheet imported successfully!");
    }

    public function export($event_id)
    {
        $event = Event::findOrFail($event_id);
        return Excel::download(new PostsExport($event_id), 'tickets.xlsx');
    }


    // OPAY
    public function online_payment($data){
        $payment = new OpayPayment();
        $response = $payment->pay(
            $data['amount'],
            null,
            $data['user_first_name'],
            $data['user_last_name'],
            $data['user_email'],
            $data['user_phone']
        );
        $post = Post::find($data['order_id']);
        $post->external_service_provider_payment_method = 'opay';
        $post->external_service_provider_order_id = $response['payment_id'];
        $post->save();
        return redirect($response['redirect_url']);
    }

    // OPAY
    public function verify_payment(Request $request){
        $payment = new OpayPayment();
        $response = $payment->verify($request);
        if($response['process_data']['data']['status'] == 'SUCCESS'){
            $post = Post::where('external_service_provider_order_id',$response['payment_id'])->first();
            $this->accept(null,$post->id,true);
            return view('thank_you', ['status_success' => 'Thank you for registering at EGYcon. An email will be sent to you with your ticket(s)']);
        }else{
            return view('thank_you', ['status_error' => 'There was an error processing your payment. Please try again later.']);
        }
    }

    // KASHIER
    public function payment_success(Request $request){
        $payment = new KashierPayment();
        $response = $payment->verify($request);
        if($response['process_data']['paymentStatus'] != 'SUCCESS'){
            return view('thank_you', ['status_error' => 'There was an error processing your payment. Please try again later.']);
        }
        $post = Post::where('order_reference_id',$response['payment_id'])->first();
        $post->external_service_provider_payment_method = 'kashier';
        $post->external_service_provider_order_id = $request->merchantOrderId;
        // check if the order is already accepted
        if($post->status != 1){
            $this->accept(null,$post->id,true);
        }
        $event = Event::find($post->event_id);
        $theme = $event->themes()->where('is_active', 1)->first();
        return view('thank_you', ['status_success' => "Thank you for registering at {$event->name}. An email will be sent to you with your ticket(s)", 'theme' => $theme, 'event' => $event]);
    }

    // onspot registration
    public function onspot_registration($event_id){
        $event = Event::findOrFail($event_id);
        $ticket_types = $event->ticket_types()->where('is_active',1)->get();
        return view('admin.onspot_registration',compact('event','ticket_types'));
    }

    // the onspot registration is done by the admin and the user is not required to pay
    // the admin can choose the multiple ticket with the quantity
    public function onspot_registration_post(Request $request,$event_id){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'ticket_type' => 'required|array',
            'quantity' => 'required|array',
            'ticket_type.*' => 'required|exists:ticket_types,id',
            'quantity.*' => 'required|integer|min:1',
        ]);
        $event = Event::findOrFail($event_id);
        $post = new Post();
        $post->event_id = $event_id;
        $post->name = $request->name;
        $post->email = $request->email;
        $post->phone_number = $request->phone;
        $post->status = 1;
        $post->payment_method = 'cash';
        $post->save();
        // TODO: add the ticket types to the post
        $total_price = 0;
        foreach($request->ticket_type as $key => $ticket_type_id){
            for($i = 0; $i < $request->quantity[$key]; $i++){
                $ticket_type = TicketType::findOrFail($ticket_type_id);
                $post_ticket = new PostTicket();
                $post_ticket->post_id = $post->id;
                $post_ticket->ticket_type_id = $ticket_type_id;
                $post_ticket->status = 1;
                $post_ticket->save();
                $total_price += $ticket_type->price;
            }
        }
        return redirect()->back()->with('success',"The user has been registered successfully!");
    }


    // private function send_email_with_template($post, $type = 'approved', $data = [], $event_id){
    //     $event = Event::find($post->event_id);
    //     // $ticket_type = TicketType::find($post_ticket->ticket_type_id);


    //     Mail::to($post->email)->send(new TicketEmail($body,$subject));
    // }
}
