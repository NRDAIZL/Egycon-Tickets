<?php

namespace App\Services\Telegram;

use App\Exceptions\UserNotFoundException;
use App\Models\TelegramChat;
use App\Models\User;
use Doctrine\Common\Cache\Psr6\InvalidArgument;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use stdClass;

class TelegramService 
{
    protected $API_KEY;
    private $url;
    private $user;
    private $chat_id;

    private $markup;
    public function __construct(User $user = null, $chat_id = null, $withoutUser = false){
        $this->API_KEY = env('TELEGRAM_API_KEY');
        if($chat_id == null){
            if($user == null){
                throw new InvalidArgumentException("User or chat_id cannot be null!");
            }
            $chat_id = $user->getTelegramChatId();
            if($chat_id == null){
                throw new UserNotFoundException("User not found!");
            }
        }else if ($withoutUser == false){
            $telegramChat = TelegramChat::where('chat_id', $chat_id)->first();
            if($telegramChat == null){
                throw new UserNotFoundException('User not found!');
            }
            $user = $telegramChat->user;
        }
        $this->url = "https://api.telegram.org/bot{$this->API_KEY}";
        $this->user = $user;
        $this->chat_id = $chat_id;
    }

    public static function withChatID($chat_id, $withoutUser = false){
        return new self(null, $chat_id, $withoutUser);
    }
    
    public static function withUser(User $user){
        return new self($user);
    }

    public function bot($method, $data = [])
    {
        $data = json_decode(json_encode($data), true);
        $url = "{$this->url}/$method";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        Log::info("TelegramService: {$method} - {$this->chat_id} - result: ".$res);
        if (curl_error($ch)) {
            Log::error(curl_error($ch));
        } else {
            curl_close($ch);
            return json_decode($res, true);
        }
        curl_close($ch);
    }

    private function addMarkUp($data){
        if($this->markup){
            $data['reply_markup'] = json_encode($this->markup);
        }
        return $data;
    }

    public function sendMessage($text){
        $data = [
            'chat_id' => $this->chat_id,
            'text' => $text,
        ];
        $data = $this->addMarkUp($data);
        return $this->bot('sendMessage', $data);
    }

    public function sendPhoto($photo){
        return $this->bot('sendPhoto', [
            'chat_id' => $this->chat_id,
            'photo' => $photo
        ]);
    }

    public function send(TelegramMessage $message){
        if ($message->action) {
            $this->markup = new stdClass();
            $this->markup->inline_keyboard = [];
            $this->markup->inline_keyboard[]= [$message->action->toArray()];
        }
        return $this->sendMessage($message->toString());
    }
}

