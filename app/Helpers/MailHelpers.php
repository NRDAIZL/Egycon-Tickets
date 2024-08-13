<?php

namespace App\Helpers;

use App\Exceptions\MailException;
use App\Exceptions\WrongValueException;
use App\Models\EventEmailTemplate;
use Exception;
use Postmark\Models\DynamicResponseModel;
use Postmark\PostmarkClient;
use Postmark\Models\PostmarkException;
use stdClass;
use Illuminate\Support\Facades\Log;

final class MailHelpers 
{
    private static $subjects = [
        "declined" => "Your request has been declined",
        "accepted" => "Your ticket request has been approved!",
        "approved" => "Your ticket request has been approved!",
        "reservation" => "Your request has been approved!",
    ];

     public static function send_declined_email($request, $email_template = null)
    {
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
                // 'Headers' => ["X-CUSTOM-HEADER" => "Header content"],
                'MessageStream' => "outbound" // here you can set your custom Message Stream
            ];

            $sendResult = self::sendEmail([$message]);
        }
    }

    public static function send_email($ticket,$request, $email_template = null){
        if(str_contains(strtolower($ticket->ticket_type->type),'noticket')){
            return;
        }
        if (str_contains(strtolower($ticket->ticket_type->type), 'discount')) {
            return;
        }
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
        } else if ($ticket->discount_code_id != null) {
            $data["code"] = $ticket->discount_code->code;
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
            $sendResult = self::sendEmail([$message]);
        }
    
    }

    /**
     * @param array $message
     * @return DynamicResponseModel | null
     * @throws MailException
     * @throws Exception
     * @throws PostmarkException
     */
    public static function sendEmail($message){
        
        if(env('ENABLE_EMAIL_SENDING', true) == false) {
            Log::channel('emails')->info(
                "Email Sending is Disabled."
                . PHP_EOL
                ."Email To Send:". 
                json_encode($message)
            );
            return null;
        }
        try {
            $client = new PostmarkClient(env("POSTMARK_TOKEN"));
            return $client->sendEmailBatch($message);
            // $sendResult = $client->sendEmailWithTemplate(
            //     "egycon@gamerslegacy.net",
            //     $request->email,
            //     27132435,
            //     [
            //         "name" => explode(' ', $request->name)[0],
            //         "order_id" => $request->id,
            //     ]
            // );
        } catch (PostmarkException $ex) {
            // If the client is able to communicate with the API in a timely fashion,
            // but the message data is invalid, or there's a server error,
            // a PostmarkException can be thrown.
            throw new MailException("Postmark Exception: ".$ex->httpStatusCode. " Status returned: ". $ex->message, $ex->postmarkApiErrorCode, $ex);
        } catch (Exception $generalException) {
            throw new MailException("A general exception thrown", 500, $generalException);
            // A general exception is thrown if the API
            // was unreachable or times out.
        }
    } 
    public static function getEmailTemplate($event_id, $type){
        $type = strtolower($type);
        if (!in_array($type, ['declined', 'reservation', 'approved'])) {
            throw new WrongValueException("Invalid Email Template Type: ". $type);
        }
        $email_template = EventEmailTemplate::where('event_id', $event_id)->where('type', $type)->first();
        if (!$email_template) {
            // get html value from assets
            $body = file_get_contents(public_path("emails/$type.html"));
            $subject = self::$subjects[$type];
            $email_template = new stdClass();
            $email_template->body = $body;
            $email_template->subject = $subject;
        }
        return $email_template;
    }
    public static function getDeclinedEmailTemplate($event_id){
        return self::getEmailTemplate($event_id, 'declined');
    }

    public static function getReservationEmailTemplate($event_id){
        return self::getEmailTemplate($event_id, 'reservation');
    }

    public static function getApprovedEmailTemplate($event_id){
        return self::getEmailTemplate($event_id, 'approved');
    }
}
