<?php

namespace App\Http\Controllers;

use App\Services\Telegram\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    
    public function index(){
        $update = json_decode(file_get_contents('php://input'));
        $message = @$update->message;
        $text = @$message->text;
        $chat_id = @$message->chat->id;
        $data = @$update->callback_query->data;
        $chat_id2 = @$update->callback_query->message->chat->id;
        $type = @$message->chat->type;
        $chat_member = @$update->chat_member->user->id;
        $new_chat_members = @$message->new_chat_members;

        $chat_id = $chat_id ?? $chat_id2;

        $type2 = @$update->callback_query->message->chat->type;
        $type = $type ?? $type2;
        Log::info($chat_id);
        
        try{
            $telegramService = TelegramService::withChatID($chat_id);
            $telegramService->sendMessage("Test 1234");
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
    }
}
