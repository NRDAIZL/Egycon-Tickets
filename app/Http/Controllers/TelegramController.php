<?php

namespace App\Http\Controllers;

use App\Exceptions\UserNotFoundException;
use App\Models\User;
use App\Services\Telegram\TelegramService;
use App\Services\Telegram\TelegramUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function index(){
        $message = new TelegramUpdate(json_decode(file_get_contents('php://input')));
        $response = null;
        try{
            $telegramService = TelegramService::withChatID($message->getChatId());
            $response = $this->reply($message->getText(), $telegramService);
        }catch(UserNotFoundException $e){
            $telegramService = TelegramService::withChatID($message->getChatId(), true);
            $response = $this->reply($message->getText(), $telegramService);
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
        if(config('app.telegram_message_testing')){
            return response()->json($response);
        }
    }

    private function reply($text, $telegramService){
        switch($text){
            case 'Checkup':
                return $telegramService->sendMessage("Messages are working");
            default:
                return $this->validateTelegramCode($text, $telegramService);
        }
    }

    private function validateTelegramCode($code, TelegramService $telegramService){
        $code = str_replace("/start ", "", $code);
        if(!$telegramService->getUser()){
            if(User::where("telegram_code", $code)->exists()){
                $user = User::where("telegram_code", $code)->first();
                $user->saveTelegramChatId($telegramService->getChatID());
                return $telegramService->sendMessage("User registered successfully!");
            }
            return $telegramService->sendMessage("User is not recognized!\nPlease configure telegram notifications on your account first");
        }
        return $telegramService->sendMessage("Unkown Command!");
    }
}
