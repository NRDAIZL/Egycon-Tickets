<?php
namespace App\Http\Controllers;

use App\Exports\PostsExport;
use App\Http\Controllers\API\EventController;
use App\Imports\PostImport;
use App\Mail\TicketEmail;
use App\Models\Event;
use App\Models\EventEmailTemplate;
use App\Models\EventPaymentMethod;
use App\Models\ExternalServiceProvider;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostTicket;
use App\Models\TicketDiscountCode;
use App\Models\TicketType;
use Carbon\Carbon;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Exception;
use Illuminate\Contracts\Session\Session;
use Postmark\PostmarkClient;
use Postmark\Models\PostmarkException;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Nafezly\Payments\Classes\KashierPayment;
use Nafezly\Payments\Classes\OpayPayment;
use stdClass;

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

    public function instructions(Request $request, $x_event_id){
        // check if time is between registration start and end time
        $event = Event::findOrFail($x_event_id);
        // get event theme
        $theme = $event->themes()->where('is_active',1)->first();
        $ticket_types = $event->ticket_types()->where('is_active',1)->get();
        if($ticket_types->count() == 0 ){
            return view('tickets_suspended');
        }
        if(session()->get('errors')){
            $questions = $event->questions;
            return view('form', ['payment_method' => $request->payment_method, 'ticket_types' => $ticket_types, 'quantity' => old('quantity'), 'total' => old('total'), 'questions' => $questions, 'theme' => $theme, 'event' => $event]);
        }
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
        return view('instructions', $data);
    }

    public function delete_all_view(){
        $random_string = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4);
        return view('admin.reset-tickets',['random_string'=>$random_string]);
    }
    
    public function delete_all(Request $request, $event_id){
        if(strtoupper($request->random_string) != strtoupper($request->random_string_confirm)){
            return redirect()->back()->with('error','The text does not match. Please try again');
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

    public function instructions_store(Request $request, $x_event_id)
    {
        if($request->has('name')){
            return $this->store($request, $x_event_id);
        }
        $payment_method = $request->payment_method;
        if(!$payment_method){
            return redirect()->back()->with('error','Please select a payment method');
        }
        $request->validate([
            'quantity'=>'array',
            'quantity.*'=>'numeric|min:0|max:10',
        ]);
        $total = 0;
        $event = Event::findOrFail($x_event_id);
        $ticket_types = $event->ticket_types;
        $i = 0;
        foreach($ticket_types as $ticket_type){
            $price = $ticket_type->price * $request->quantity[$i];
            $total += $price;
            $i++;
        }
        if($total==0){
            return redirect()->back()->with('error','You must select at least on ticket');
        }
        $theme = $event->themes()->where('is_active', 1)->first();
        $questions = $event->questions;
        return view('form', ['payment_method'=>$request->payment_method,'ticket_types' => $ticket_types,'total'=>$total,'quantity'=>$request->quantity,'theme'=>$theme, 'event'=>$event, 'questions'=>$questions]);
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

    public function store(Request $request, $x_event_id)
    {
        $request->validate([
            'quantity'=>'array|required',
            'quantity.*'=>'numeric|min:0|max:10',
            'total'=>"numeric",
            'name'=>"required|string|min:6|max:64",
            'email' => "required|email",
            'phone_number' => "required",
            'payment_method'=>"required",
        ]);
        if ($request->payment_method == "vodafone_cash") {
            $request->validate([
                'receipt' => "required|file|mimes:png,jpg,jpeg",
            ]);
        }
        $event = Event::findOrFail($x_event_id);
        $questions  = $event->questions;
        if (strpos(trim($request->name), ' ') === false) {
            $ticket_types = $event->ticket_types;
            session()->flash('status-failure', 'Please enter your full name.');
            session()->flashInput($request->input());
            return view('form', ['ticket_types' => $ticket_types, 'total' => $request->total, 'quantity' => $request->quantity, 'payment_method'=>$request->payment_method, 'questions' => $questions]);
        }

        // get submited question answers and store them in an array
        $answers = [];
        foreach($questions as $question){
            $answers[$question->question] = $request->input('question_'.$question->id);
        }

        $theme = $event->themes()->where('is_active', 1)->first();

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
        $unique_id = uniqid();
       
        $post->code = $unique_id;
        $post->save();
        $j=0;
        $tickets = Event::findOrFail($x_event_id)->ticket_types;
        foreach($request->quantity as $quantity){
            $ticket = $tickets[$j];
            for($i = 0; $i<$quantity*$ticket->person; $i++){
                $unique_id = $this->generate_random_string(6);
                $post_ticket = new PostTicket();
                $post_ticket->post_id = $post->id;
                $post_ticket->ticket_type_id = $ticket->id;
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
                    $qrcode->render($unique_id, public_path('images/qrcodes/' . $unique_id . ".jpg"));
                } else if ($ticket->type == "discount") {
                    $discount_ticket = TicketDiscountCode::where('claimed_at', null)->first();
                    if ($discount_ticket) {
                        $post_ticket->discount_code_id = $discount_ticket->id;
                        $discount_ticket->claimed_at = Carbon::now();
                        $discount_ticket->save();
                    } else {
                        $post->delete();
                        return redirect()->back()->with(["error" => "An error occurred while processing your request. Please try again later. Error code: 1", 'theme' => $theme, 'event' => $event]);
                    }
                } else {
                    $post->delete();
                    return redirect()->back()->with(["error" =>"An error occurred while processing your request. Please try again later. Error code: 2", 'theme' => $theme, 'event' => $event]);
                }
                $post_ticket->save();
            }
            $j++;
        }
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
        if($request->payment_method == "credit_card"){
            $post->save();
            // calculate the total amount
            $total = 0;
            $j=0;
            foreach($request->quantity as $quantity){
                $ticket = $tickets[$j];
                $total += $quantity*$ticket->price;
                $j++;
            }
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
                return redirect()->back()->with(["error" =>"An error occurred while processing your request. Please try again later. Error code: 3", 'theme' => $theme, 'event' => $event]);
            }
            $event_payment_method = EventPaymentMethod::where('event_id',$x_event_id)->where('payment_method_id',$payment_methods->id)->first();
            if(!$event_payment_method){
                return redirect()->back()->with(["error" =>"An error occurred while processing your request. Please try again later. Error code: 4", 'theme' => $theme, 'event' => $event]);
            }
            return view('kashier', ['data' =>$data, 'theme' => $theme, 'event' => $event, 'event_payment_method' => $event_payment_method]);
        }

        return view('thank_you', ['status_success' => 'Thank you for registering at Egycon. An email will be sent to you once your request is reviewed.', 'total' => $request->total, 'quantity' => $request->quantity, 'theme' => $theme, 'event' => $event]);
    }
    private function send_email($ticket,$request, $email_template = null){
        if(str_contains(strtolower($ticket->ticket_type->type),'noticket')){
            return;
        }
        if (str_contains(strtolower($ticket->ticket_type->type), 'discount')) {
            return;
        }
        // check if ticket is a BUS ticket (no email will be sent)
        if (str_contains(strtolower($ticket->ticket_type->type), 'bus')) {
            return;
        }
        try {
            $client = new PostmarkClient(env("POSTMARK_TOKEN"));
            $data = [
                "name" => explode(' ', $request->name)[0],
                "ticket_type" => $ticket->ticket_type->name . " Ticket - " . $ticket->ticket_type->price,
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
                    'Headers' => ["X-CUSTOM-HEADER" => "Header content"],
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

    public function view_requests(Request $request,$event_id){
        $q = false;
         $statusPriorities = [1, 0];

        $evnt = Event::find($event_id);
        if($evnt == null){
            return redirect()->back()->with(["error" => "An error occurred while processing your request. Please try again later. Error code: 3"]);
        }
        $posts = $evnt->posts()->with(['ticket.ticket_type','ticket_type','provider'])->orderByRaw('FIELD (status, ' . implode(', ', $statusPriorities) . ') ASC');
        if($request->has('q')){
            $q = $request->q;
            $posts = $posts->where(function($query) use ($q){
                return $query->orWhere('email', 'like', '%' . $q . '%')->orWhere('phone_number', 'like', '%' . $q . '%')->orWhere('id', 'like', '%' . $q . '%')->orWhere('name', 'like', '%' . $q . '%');
            })->paginate(1000);
        }else{
            $posts = $posts->where('event_id',$event_id)->orderBy('created_at',"DESC")->limit(1000)->paginate(15);
        }
        return view('admin.requests',['requests'=>$posts, 'query'=>$q]);
    }

    public function accept($event_id = null,$id, $through_payment = false){
        $post = Post::with('ticket.ticket_type','event')->findOrFail($id);
        if(!$through_payment){
            $user_events = auth()->user()->events()->pluck('event_id')->toArray();
            // check if post event is in user events
            if (!in_array($post->event_id, $user_events)) {
                return redirect()->back()->with(["error" => "You are not allowed to view this page!"]);
            }
        }
        
        foreach($post->ticket as $ticket){
            $email_template = EventEmailTemplate::where('event_id',$post->event_id)->where('type','approved')->first();
            if(!$email_template){
                // get html value from assets 
                $body = file_get_contents(public_path('emails/approved.html'));
                $subject = "Your ticket has been approved!";
                $email_template = new stdClass();
                $email_template->event_id = $post->event_id;
                $email_template->type = 'approved';
                $email_template->body = $body;
                $email_template->subject = $subject;
            }
            $this->send_email($ticket,$post, $email_template);
        }
        $post->status = 1;
        $post->save();
        return redirect()->back()->with(["success"=>"{$post->name}'s request has been accepted successfully!"]);
    }

    public function view_tickets($event_id,$id){
        $post = Post::with('ticket.ticket_type')->findOrFail($id);
        // get all event ids through ticket_types
        $event_ids = $post->ticket_type->pluck('event_id')->toArray();
        // get all events that user has access to 
        $user_events = auth()->user()->events()->pluck('event_id')->toArray();
        // check if all event ids are in user events
        if (!empty(array_diff($event_ids, $user_events))) {
            return redirect()->back()->with(["error" => "You are not allowed to view this page!"]);
        }
        return view('admin.view_tickets',['post'=>$post]);
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
    public function reject($event_id=null,$id, $through_payment = false)
    {
        $post = Post::with('ticket.ticket_type','event')->findOrFail($id);
        if (!$through_payment) {
            $user_events = auth()->user()->events()->pluck('event_id')->toArray();
            // check if post event is in user events
            if (!in_array($post->event_id, $user_events)) {
                return redirect()->back()->with(["error" => "You are not allowed to view this page!"]);
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
            return redirect()->back()->with(["error" => "You are not allowed to view this page!"]);
        }
        foreach($post->ticket as $ticket){
            $ticket->delete();
        }
        $post->delete();
        return redirect()->back()->with(["success" => "{$post->name}'s request has been rejected successfully!"]);
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
                        return redirect()->back()->with(["error"=>"There are no discount tickets left!"]);
                    }
                }else{
                    $post->delete();
                    return redirect()->back()->with(["error" => "There are no settings enabled for ticket codes!"]);
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
            $data['user_phone'],
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
            return view('thank_you', ['status_success' => 'Thank you for registering at Egycon. An email will be sent to you with your ticket(s)']);
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