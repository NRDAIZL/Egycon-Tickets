<?php
namespace App\Http\Controllers;

use App\Imports\PostImport;
use App\Models\ExternalServiceProvider;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostTicket;
use App\Models\TicketType;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Exception;
use Illuminate\Contracts\Session\Session;
use Postmark\PostmarkClient;
use Postmark\Models\PostmarkException;
use DB;
use Maatwebsite\Excel\Facades\Excel;

class PostController extends Controller
{
    public function instructions(Request $request){
        $ticket_types = TicketType::all();
        if(session()->get('errors')){
            return view('form', ['ticket_types' => $ticket_types, 'quantity' => old('quantity'), 'total' => old('total')]);
        }
        return view('instructions',['ticket_types'=>$ticket_types, ]);
    }
    public function instructions_store(Request $request)
    {
        if($request->has('name')){
            return $this->store($request);
        }
        $request->validate([
            'quantity'=>'array',
            'quantity.*'=>'numeric|min:0|max:10',
        ]);
        $total = 0;
        $ticket_types = TicketType::all();
        $i = 0;
        foreach($ticket_types as $ticket_type){
            $price = $ticket_type->price * $request->quantity[$i];
            $total += $price;
            $i++;
        }
        if($total==0){
            return redirect()->back()->with('error','You must select at least on ticket');
        }
        
        return view('form', ['ticket_types' => $ticket_types,'total'=>$total,'quantity'=>$request->quantity]);
    }

    public function edit_requests()
    {
        return view('admin.edit-requests');
    }

    public function action()
    {
        $data = PostTicket::with('post','ticket_type')->where('code',$_POST['code'])->first();
        if(!$data){
            return back()->with('error', 'Code # '.$_POST['code'].' Not Found');
        }else if(str_contains(strtolower($data->ticket_type->name),'bus')){
            return back()->with('error', 'Code # ' . $_POST['code'] . ' is a Bus Ticket. This page is for event tickets only');
        }else{
            // return back()->with('message', 'Code # '.$_POST['code'].' Found');
            $status = $data->post->status;
            if($status == 0 || $status == null){
                return back()->with('error', 'Not accepted yet');
            }else if($status == 1 && $data->status != 2){
                $data->status = 2;
                $data->save();
                    return back()->with('message', 'Scanned Successfully! The registree can enter Egycon!');
            }else if($data->status == 2){
                return back()->with('error', 'Already Scanned Before!!!');
            }else{
                return back()->with('error', 'There was a problem Scanning! Please refer to the Technical Support Team.');
            }
        }

    }

    public function store(Request $request)
    {
        $request->validate([
            'quantity'=>'array|required',
            'quantity.*'=>'numeric|min:0|max:10',
            'total'=>"numeric",
            'name'=>"required|string|min:6|max:64",
            'email' => "required|email",
            'phone_number' => "required",
            'receipt'=>"required|file|mimes:png,jpg,jpeg",
        ]);
        if (strpos(trim($request->name), ' ') === false) {
            $ticket_types = TicketType::all();
            session()->flash('status-failure', 'Please enter your full name.');
            session()->flashInput($request->input());
            return view('form', ['ticket_types' => $ticket_types, 'total' => $request->total, 'quantity' => $request->quantity, ]);
        }
        $post = new Post;
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
       
        $post->phone_number = $request->phone_number;
        if(preg_match('@[0-9]@', $post->phone_number) == 0 ){
            return redirect()->back()->with('status-failure', 'Phone number must be numbers only!');
        }
        $unique_id = uniqid();
       
        $post->code = $unique_id;
        $post->save();
        $j=0;
        $tickets = TicketType::all();
        foreach($request->quantity as $quantity){
            $ticket = $tickets[$j];
            for($i = 0; $i<$quantity*$ticket->person; $i++){
                $unique_id = uniqid();
                $post_ticket = new PostTicket();
                $post_ticket->post_id = $post->id;
                $post_ticket->ticket_type_id = $ticket->id;
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
                $post_ticket->save();
            }
            $j++;
        }
        return view('thank_you', ['status-success' => 'Thank you for registering at Egycon 9. An email will be sent to you once your request is reviewed.', 'total' => $request->total, 'quantity' => $request->quantity]);
    }
    private function send_email($ticket,$request){
        if(str_contains(strtolower($ticket->ticket_type->name),'bus')){
            return;
        }

        try {
            $client = new PostmarkClient(env("POSTMARK_TOKEN"));
            $sendResult = $client->sendEmailWithTemplate(
                "info@gamerslegacy.net",
                $request->email,
                27131977,
                [
                    "name" => explode(' ', $request->name)[0],
                    "ticket_type"=> $ticket->ticket_type->name . " Ticket - " . $ticket->ticket_type->price,
                    "order_id" => $request->id,
                    // "date"=>date('Y/m/d'),
                    "qrcode"=>asset('images/qrcodes/'.$ticket->code.'.jpg'),
                    "code"=>$ticket->code
                ]
            );

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

    public function view_requests(Request $request){
        $q = false;
        $posts = Post::with(['ticket.ticket_type','ticket_type','provider'])->orderBy('status');
        if($request->has('q')){
            $q = $request->q;
            $posts = $posts->where('id',$request->q)->orWhere('email',"LIKE", "%" . $request->q . "%")->orWhere('name', "LIKE", "%" . $request->q . "%")->orWhere('phone_number',"LIKE","%".$request->q."%")->paginate(1000);
        }else{
            $posts = $posts->paginate(15);
        }
        return view('admin.requests',['requests'=>$posts, 'query'=>$q]);
    }

    public function accept($id){
        $post = Post::with('ticket.ticket_type')->findOrFail($id);
        foreach($post->ticket as $ticket){
            $this->send_email($ticket,$post);
        }
        $post->status = 1;
        $post->save();
        return redirect()->back()->with(["success"=>"{$post->name}'s request has been accepted successfully!"]);
    }
    private function send_declined_email($request)
    {

        try {
            $client = new PostmarkClient(env("POSTMARK_TOKEN"));
            $sendResult = $client->sendEmailWithTemplate(
                "info@gamerslegacy.net",
                $request->email,
                27132435,
                [
                    "name" => explode(' ', $request->name)[0],
                    "order_id" => $request->id,
                ]
            );

            // Getting the MessageID from the response
            echo $sendResult->MessageID;
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
    public function reject($id)
    {
        $post = Post::with('ticket_type')->findOrFail($id);
        $this->send_declined_email($post);
        $post->status = 0;
        $post->save();
        return redirect()->back()->with(["success" => "{$post->name}'s request has been rejected successfully!"]);
    }

    public function import_sheet(){
        $providers = ExternalServiceProvider::all();
        return view('admin.import',['providers'=>$providers]);
    }

    public function import_sheet_store(Request $request)
    {
        $request->validate([
            'sheet'=>'required|file',
            'provider_id'=>'required|exists:external_service_providers,id'
        ]);
        $array = Excel::toArray(PostImport::class,$request->file('sheet'));
        
        foreach($array[0] as $k=>$row){
            if($k == 0 || Post::where('external_service_provider_order_id',$row[0])->where('external_service_provider_id',$request->provider_id)->first()){
                continue;
            }
            $post = new Post();
            $post->external_service_provider_id = $request->provider_id;
            $post->external_service_provider_order_id = $row[0];
            $post->name = $row[1];
            $post->phone_number = $row[2];
            $post->email = $row[3];
            $post->external_service_provider_payment_method = $row[6];
            $post->external_service_provider_notes = $row[7];

            $quantity = $row[4];
            $ticket_type = TicketType::where('name',$row[5])->first();
            $unique_id = uniqid();
            $post->code = $unique_id;
            $post->picture = 0;
            $post->status=1;
            $post->save();
            for ($i = 0; $i < $quantity * $ticket_type->person; $i++) {
                $unique_id = uniqid();
                $post_ticket = new PostTicket();
                $post_ticket->post_id = $post->id;
                $post_ticket->ticket_type_id = $ticket_type->id;
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
                $post_ticket->save();
                $post_ticket = PostTicket::with('ticket_type')->find($post_ticket->id);
                $this->send_email($post_ticket, $post);

            }
            
        }
        return redirect()->back()->with('success',"Sheet imported successfully!");
    }
}