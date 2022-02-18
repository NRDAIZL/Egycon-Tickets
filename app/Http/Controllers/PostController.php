<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostTicket;
use App\Models\TicketType;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Exception;
use Postmark\PostmarkClient;
use Postmark\Models\PostmarkException;

class PostController extends Controller
{
    public function instructions(){
        $ticket_types = TicketType::all();
        return view('instructions',['ticket_types'=>$ticket_types]);
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
        $post = new Post;
        // check that the selected file is image and save it to a folder
        // $post->receipt = $request->receipt;
        if($request->hasFile('receipt')){
            $image = $request->file('receipt');
            $image_name = $image->getClientOriginalName();
            $image->move(public_path('/images'), $image_name);
            $post->picture = $image_name;
        }
        $post->name = $request->name;
        $post->ticket_type_id = $request->ticket_type_id;
        // check that the name is chars only
        if(preg_match("^[a-zA-Z]+(([',. -][a-zA-Z ])?[a-zA-Z])$^", $post->name) == 0){
           return redirect()->back()->with('status-failure', 'Name should be characters only!');
        }
        // check that it is a correct type of emails
        $post->email = $request->email;
        if (!filter_var($post->email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('status-failure', 'Not a valid email address!');
        }
        // check that it is numbers only
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
            for($i = 0; $i<$quantity; $i++){
                $unique_id = uniqid();
                $ticket = $tickets[$j];
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
        return redirect()->back()->with('status-success', 'Thank you for registering at Egycon 9. An email will be sent to you once your request is reviewed.');
    }
    private function send_email($request){
        try {
            $client = new PostmarkClient(env("POSTMARK_TOKEN"));
            $sendResult = $client->sendEmailWithTemplate(
                "info@gamerslegacy.net",
                $request->email,
                26959536,
                [
                    "name"=>explode(' ',$request->name)[0],
                    "ticket_type"=>$request->ticket_type->name." Ticket - ". $request->ticket_type->price,
                    "date"=>date('Y/m/d'),
                    "action_url"=>"#",
                    "qrcode"=>"#"
                ]
            );

            // Getting the MessageID from the response
            echo $sendResult->MessageID;
        } catch (PostmarkException $ex) {
            // If the client is able to communicate with the API in a timely fashion,
            // but the message data is invalid, or there's a server error,
            // a PostmarkException can be thrown.
            echo $ex->httpStatusCode;
            echo $ex->message;
            echo $ex->postmarkApiErrorCode;
        } catch (Exception $generalException) {
            // A general exception is thrown if the API
            // was unreachable or times out.
        }
    }
    public function view_requests(){
        $posts = Post::with('ticket_type')->orderBy('status')->paginate(15);
        return view('admin.requests',['requests'=>$posts]);
    }

    public function accept($id){
        $post = Post::with('ticket_type')->findOrFail($id);
        $this->send_email($post);
        $post->status = 1;
        $post->save();
        return redirect()->back()->with(["success"=>"{$post->name}'s request has been accepted successfully!"]);
    }
    public function reject($id)
    {
        $post = Post::with('ticket_type')->findOrFail($id);
        $post->status = 0;
        $post->save();
        return redirect()->back()->with(["success" => "{$post->name}'s request has been rejected successfully!"]);
    }
}