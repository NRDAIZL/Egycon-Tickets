<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\TicketType;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
class PostController extends Controller
{
    
    public function view()
    {
        $ticket_types = TicketType::all();
        return view('form',["ticket_types"=>$ticket_types]);
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'name'=>"required|string|min:6|max:64",
            'email' => "required|email|unique:posts,email",
            'phone_number' => "required",
            'receipt'=>"required|file|mimes:png,jpg,jpeg",
            'ticket_type_id'=>"required|integer|exists:ticket_types,id",
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
        // check that the name is chars only
        if(preg_match("^[a-zA-Z]+(([',. -][a-zA-Z ])?[a-zA-Z])$^", $post->name) == 0){
           return redirect()->back()->with('status-failure', 'First name should be characters only!');
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
        $qr_options = new QROptions([
            'version'    => 5,
            'outputType' => QRCode::OUTPUT_IMAGE_JPG,
            'eccLevel'   => QRCode::ECC_L,
            'imageTransparent'=>false,
            'imagickFormat'=>'jpg',
            'imageTransparencyBG'=>[255,255,255],
        ]);        
        $qrcode = new QRCode($qr_options);
        $qrcode->render($unique_id, public_path('images/qrcodes/'.$unique_id.".jpg"));
        $post->code = $unique_id;
        $post->save();
        return redirect()->back()->with('status-success', 'Thank you for registering at Egycon 9. An email will be sent to you once your request is reviewed.');
    }
}