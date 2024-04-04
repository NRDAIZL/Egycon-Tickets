<?php
namespace App\Http\Controllers;

use App\Exceptions\InvalidPromoCodeException;
use App\Exceptions\InvalidRequestException;
use App\Helpers\MailHelpers;
use App\Helpers\QRHelper;
use App\Helpers\RequestsHelper;
use App\Helpers\TicketsHelper;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\Event;
use App\Models\PromoCode;
use App\Models\PostTicket;
use App\Models\TicketType;
use App\Imports\PostImport;
use App\Exports\PostsExport;
use App\Helpers\CommonUtils;
use App\Helpers\HttpHelper;
use App\Helpers\PaymentHelper;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\SubTicketType;
use App\Models\EventPaymentMethod;
use App\Models\TicketDiscountCode;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ExternalServiceProvider;
use Nafezly\Payments\Classes\KashierPayment;
use App\Http\Controllers\API\EventController;

class PostController extends Controller
{

    protected $event;
    protected $ticketsHelper;
    // construct
    public function __construct(Event $event = null, Request $request){
        $this->event = $event;
        $this->ticketsHelper = new TicketsHelper($request, $event);
    }

    
    public function instructions_code(Request $request, $x_event_id){
        return $this->instructions($request, $x_event_id, true);
    }


    public function instructions(Request $request, $x_event_id, $i_have_a_code = null){
        /** @var Event $event */
        $event =  $this->event;
        if($event == null){
            abort(404);
        }
        
        if(is_numeric($x_event_id)){
            if($event->slug != null){
                if($i_have_a_code)
                    return redirect()->route('promo_code', ['x_event_id' => $event->slug]);
                else
                    return redirect()->route('instructions', ['x_event_id' => $event->slug]);
            }
        }else{
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
        $data['payment_methods'] = EventPaymentMethod::getAndMapMethods($x_event_id);
        $data['i_have_a_code'] = $i_have_a_code;
        return view('instructions', $data);
    }

    public function instructions_code_store(Request $request, $x_event_id){
        $event = $this->event;
        if ($event == null) {
            return abort(404);
        }
        $x_event_id = $event->id;
        if ($request->has('name')) {
            return $this->store($request, $x_event_id);
        }
        $request->validate([
            'code' => 'required',
        ]);
        try{
            $code = PromoCode::getAndValidateEventCode($request->code, $x_event_id);
        } catch(InvalidPromoCodeException $e){
            return redirect()->back()->with('status-failure', $e->getMessage());
        }
        return redirect()->route('promo_code_tickets',['x_event_id'=>$x_event_id,'code'=>$request->code]);
    }

    public function instructions_code_show_tickets($x_event_id, $code){
        /** @var Event $event */
        $event = $this->event;
        if ($event == null) {
            return abort(404);
        }
        $x_event_id = $event->id;

        try{
            $code = PromoCode::getAndValidateEventCode($code, $x_event_id);
        } catch(InvalidPromoCodeException $e){
            return redirect()->back()->with('status-failure', $e->getMessage());
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
        $payment_methods = EventPaymentMethod::getAndMapMethods($x_event_id);
        $data['payment_methods'] = $payment_methods;
        $data['i_have_a_code'] = false;
        $data['code'] = $code;
        if($code->discount > 0)
            $data['discount'] = $code->discount;
        return view('instructions', $data);
    }
    public function register_request_with_promo_view(Request $request, $x_event_id){
        return $this->register_request_view($request, $x_event_id);
    }

    public function register_request_view(Request $request, $x_event_id, $code = null)
    {
        $event = $this->event;
        if ($event == null) {
            return abort(404);
        }
        $x_event_id = $event->id;

        if ($request->has('name')) {
            return $this->store($request, $x_event_id);
        }

        $ticketTypes = $event->ticket_types()->where(['is_visible'=> 1])->orderBy('is_disabled')->get();

        $theme = $event->themes()->where('is_active', 1)->first();
        $questions = $event->questions;
        $promoCode = null;
        if($request->has('promo_code')){
            try{
                $promoCode = PromoCode::getAndValidateEventCode($request->promo_code, $x_event_id);
            } catch(InvalidPromoCodeException $e){
                return HttpHelper::redirectError($e->getMessage());
            }
            $ticketTypes = $promoCode->ticket_types;
        }
        $request->validate([
            'quantity' => 'array',
            'quantity.*' => 'numeric|min:0|max:10',
        ]);
        $totalPrice = 0;
        $totalQuantity = array_sum($request->quantity);
        try{
            $total_reservation_ticket = $this->ticketsHelper->countAndValidateReservations($ticketTypes);
        } catch(InvalidRequestException $e){
            return HttpHelper::redirectError($e->getMessage());
        }
        $requestIncludeReservations = $total_reservation_ticket > 0;
        $totalPrice = round(array_reduce(array_chunk($ticketTypes->pluck('price')->toArray(), 1, true), function($sum, $item) use ($request, $promoCode){
            return CommonUtils::sum($sum, current($item) * $request->quantity[key($item)]);
        }) * (100 - optional($promoCode)->discount ?? 0)/100, 2);

        if ($totalQuantity <= 0) {
            return HttpHelper::redirectError("Please select at least one ticket");
        }

        $paymentMethod = $request->payment_method;
        if (!$paymentMethod && !$requestIncludeReservations && $totalPrice > 0) {
            return HttpHelper::redirectError("Please select a payment method");
        }
        $paymentMethod = $requestIncludeReservations ? "reservation" : $paymentMethod;
        return view('form', ['reservation'=> $requestIncludeReservations, 'payment_method' => $paymentMethod, 'ticket_types' => $ticketTypes, 'total' => $totalPrice, 'quantity' => $request->quantity, 'theme' => $theme, 'event' => $event, 'questions' => $questions, 'code' => $promoCode]);
    }

    public function delete_all_view(){
        $random_string = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4);
        return view('admin.reset-tickets',['random_string'=>$random_string]);
    }

    public function delete_all(Request $request, $event_id){
        if(strtoupper($request->random_string) != strtoupper($request->random_string_confirm)){
            return redirect()->back()->with('status-failure','The text does not match. Please try again');
        }
        $posts = $this->event->posts;
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
    }
    private function generate_post_ticket(&$post, $ticket, $theme, $event, $sub_ticket_type_id){
        $post_ticket = new PostTicket();
        $post_ticket->post_id = $post->id;
        $post_ticket->ticket_type_id = $ticket->id;
        if($sub_ticket_type_id != null){
            $post_ticket->sub_ticket_type_id = $sub_ticket_type_id;
        }
        if ($ticket->type == "qr") {
            $post_ticket->code = (new QRHelper())->generate();
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
        $requires_receipt = PaymentHelper::paymentReceiptRequired($request->payment_method);

        if($request->quantity <= 0){
            session()->flash('status-failure', 'Please select at least one ticket');
        }
        if($request->total > 0){
            $request->validate(['payment_method' => "required"]);
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
        if ($requires_receipt && $request->total > 0) {
            $request->validate([
                'receipt' => "required|file|mimes:png,jpg,jpeg",
            ]);
        }
        /** @var Event $event */
        $event = $this->event;
        if($event == null){
            return redirect()->back()->with('status-failure', 'Event not found');
        }
        $questions  = $event->questions;
        $ticketTypes = [];
        $theme = $event->themes()->where('is_active', 1)->first();
        if($request->promo_code){
            try{
                $promo = PromoCode::getAndValidateEventCode($request->promo_code, $x_event_id);
            }catch(InvalidPromoCodeException $e){
                session()->flash('status-failure', $e->getMessage());
                session()->flashInput($request->input());
                return redirect()->route('promo_code_tickets', ['event_id' => $x_event_id, 'code' => $request->promo_code]);
            }
            $ticketTypes = $promo->ticket_types;
        }else{
            // $ticket_types = $event->ticket_types;
            $ticketTypes = TicketType::where('event_id',$x_event_id)->where(['is_active' => 1, 'is_visible' => 1])->orderBy('is_disabled')->get();
        }
        if (strpos(trim($request->name), ' ') === false) {
            session()->flash('status-failure', 'Please enter your full name.');
            session()->flashInput($request->input());
            return view('form', ['ticket_types' => $ticketTypes, 'theme'=>$theme, 'total' => $request->total, 'quantity' => $request->quantity, 'payment_method'=>$request->payment_method, 'questions' => $questions, 'code' => $request->promo_code, 'event' => $event]);
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
        $totalPrice = 0;
        $totalQuantity = array_sum($request->quantity);
        try{
            $totalReservationTickets = $this->ticketsHelper->countAndValidateReservations($ticketTypes);
            $selectedSubTicketTypes = $this->ticketsHelper->getSubTicketsSelected($ticketTypes);
        }catch(InvalidRequestException $e){
            return HttpHelper::redirectError($e->getMessage());
        }
        $requestIncludeReservations = $totalReservationTickets > 0;

        // check if the same email has already created a reservation ticket in the same event
        $emailHasReservations = Post::where('email', $request->email)->where('event_id', $x_event_id)->whereHas('ticket', function($query){
            return $query->whereHas('ticket_type', function($sub_query){
                return $sub_query->where('type', 'reservation');
            });
        })->exists();
     
        if($emailHasReservations && $requestIncludeReservations) return HttpHelper::redirectError("Failed to register your request. You can't register more reservations");
        if(isset($promo)){
            if ($totalQuantity > $promo->max_uses - $promo->uses) {
                return HttpHelper::redirectError('Number of tickets exceeds limit!');
            }
        }
        $j = 0;
        foreach($request->quantity as $quantity){
            $ticket = $ticketTypes[$j];
            if(isset($promo)){
                $totalPrice += ($ticket->price - (($promo->discount / 100) * $ticket->price))*$quantity;
                $post->promo_code_id = $promo->id;
                if($promo->uses + $quantity > $promo->max_uses){
                    return HttpHelper::redirectError('Number of tickets exceeds limit!');
                }
                $promo->uses = $promo->uses + $quantity;
                if ($promo->uses >= $promo->max_uses) {
                    $promo->is_active = 0;
                }
            } else {
                $totalPrice += $quantity * $ticket->price;
            }
            for($i = 0; $i<$quantity*$ticket->person; $i++){
                $post->save();
                $sub_ticket_type = null;
                if(isset($selectedSubTicketTypes[$ticket->id])){
                    $sub_ticket_type = $selectedSubTicketTypes[$ticket->id][$i];
                }
                $response = $this->generate_post_ticket($post, $ticket, $theme, $event, $sub_ticket_type);
                if($response){
                    return $response;
                }
            }

            $j++;
        }
        if ($totalPrice > $request->total) {
            $post->delete();
            return HttpHelper::redirectError('There has been an issue with your request!');
        }
        $post->total_price = $totalPrice;

        if($request->payment_method == "reservation" && $totalQuantity == $totalReservationTickets){
           return $this->accept($x_event_id, $post->id, true);
        }
        $post->save();
        if(isset($promo)){
            $promo->save();
        }
        if ($totalPrice == 0 && isset($promo)) {
            $post->status = 1;
            $post->save();

            $this->accept($x_event_id, $post->id, true);
            return redirect()->route('thank_you', [
                'x_event_id' =>  $x_event_id,
            ]);

        }

        if ($requires_receipt && $totalPrice > 0) {
            if(!$request->hasFile('receipt')){
                return redirect()->back()->with('status-failure', 'Something wrong happened while processing your request!');
            }

        }

        $post->save();
   
        // KASHIER
        if($request->payment_method == "credit_card" && $totalPrice > 0 ){
            $post->save();
            $total = $post->total_price;
            $data = PaymentHelper::buildKashierData($total, $request->email, $request->name, $request->phone_number, $post->id, $post->order_reference_id, $x_event_id);
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
        /** @var Event $event */
        $event = $this->event;
        if($event->slug != null && $event->slug != '' && $event->slug != $x_event_id){
            return redirect()->route('thank_you', ['x_event_id' => $event->slug]);
        }
        $theme = $event->themes()->where('is_active', 1)->first();

        return view('thank_you', ['status_success' => 'Thank you for registering at EGYcon. An email will be sent to you once your request is reviewed.', 'theme' => $theme, 'event' => $event]);
    }

    

    public function view_requests(Request $request,$event_id){
        $statusPriorities = [1, 0];
        /** @var Event $evnt */
        $evnt = $this->event;
        if($evnt == null){
            return redirect()->back()->with(["status-failure" => "An error occurred while processing your request. Please try again later. Error code: 3"]);
        }
        $posts = $evnt->posts()->with(['ticket.ticket_type','ticket_type','provider'])->orderByRaw('FIELD (status, ' . implode(', ', $statusPriorities) . ') ASC');
        $search_query = $request->has('q') ? $request->q : null;
        $posts = RequestsHelper::searchRequestsForAdmin($posts, $search_query);
        $posts->distinct();
        if($request->has('q')) {
            $posts = $posts->paginate(1000);
        }else{
            $posts = $posts->paginate(15);
        }
        return view('admin.requests',['requests'=>$posts, 'query'=>$search_query ?? null]);
    }

    public function view_ticket_type_requests(Request $request, $event_id, $ticket_type_id)
    {
        $statusPriorities = [1, 0];
        $evnt = $this->event;
        if ($evnt == null) {
            return redirect()->back()->with(["status-failure" => "An error occurred while processing your request. Please try again later. Error code: 3"]);
        }
        $ticket_type = TicketType::where('id', $ticket_type_id)->where('event_id', $event_id)->withTrashed()->first();
        $posts = $ticket_type->posts()->with(['ticket.ticket_type', 'ticket_type', 'provider'])->orderByRaw('FIELD (posts.status, ' . implode(', ', $statusPriorities) . ') ASC')->orderBy('created_at', "DESC");
        $search_query = $request->has('q') ? $request->q : null;
        $posts = RequestsHelper::searchRequestsForAdmin($posts, $search_query);
        $posts->distinct();
        if ($request->has('q')) {
            $posts = $posts->paginate(1000);
        } else {
            $posts = $posts->paginate(15);
        }
        return view('admin.requests', ['requests' => $posts, 'query' => $search_query ?? null, 'requests_keyword' => $ticket_type->name, 'ticket_type_id' => $ticket_type_id]);
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
                $email_template = MailHelpers::getReservationEmailTemplate($event_id);
            }else{
                $email_template = MailHelpers::getApprovedEmailTemplate($event_id);
            }
            MailHelpers::send_email($ticket,$post, $email_template);
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
        // TODO: Check event
        if(!$ticket){
            return redirect()->back();
        }
        $ticket->scanned_at = date("Y-m-d H:i:s");
        $ticket->save();
        return redirect()->back();
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
        MailHelpers::send_declined_email($post, MailHelpers::getDeclinedEmailTemplate($event_id));
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
        $array = Excel::toArray(new PostImport, $request->file('sheet'));

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
                $post_ticket = new PostTicket();
                $post_ticket->post_id = $post->id;
                $post_ticket->ticket_type_id = $ticket_type->id;
                if(config('settings.enable_qr')){
                    $post_ticket->code = $unique_id;
                    $unique_id = (new QRHelper())->generate();
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
                MailHelpers::send_email($post_ticket, $post);
            }
        }
        return redirect()->back()->with('success',"Sheet imported successfully!");
    }

    public function export($event_id, $query = null, $ticket_id = null)
    {
        $query == "null" ? $query = null : $query;
        return Excel::download(new PostsExport($event_id, $query, $ticket_id), 'tickets.xlsx');
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
        $post->save();
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
        /** @var Event $event */
        $event = $this->event;
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
}
