<?php

namespace App\Services\Telegram;

use App\Models\TelegramChat;
use App\Models\User;

class TelegramService 
{
    protected $API_KEY;
    private $url;
    private $user;
    private $chat_id;
    public function __construct(User $user = null, $chat_id = null){
        $this->API_KEY = env('TELEGRAM_API_KEY');
        if($chat_id == null){
            if($user == null){
                throw new \InvalidArgumentException("User or chat_id cannot be null!");
            }
            $chat_id = $user->getTelegramChatId();
            if($chat_id == null){
                throw new \InvalidArgumentException("User not found!");
            }
        }else{
            $user = TelegramChat::where('chat_id', $chat_id)->first()->user;
            if($user == null){
                throw new \InvalidArgumentException('User not found!');
            }
        }
        $this->url = "https://api.telegram.org/bot{$this->API_KEY}/";
        $this->user = $user;
        $this->chat_id = $chat_id;
    }

    public static function withChatID($chat_id){
        return new self(null, $chat_id);
    }

    public static function withUser(User $user){
        return new self($user);
    }

    public function bot($method, $data = [])
    {
        $url = "{$this->url}/$method";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        if (curl_error($ch)) {
            curl_close($ch);
            dd(curl_error($ch));
        } else {
            return json_decode($res);
        }
        curl_close($ch);
    }

    public function sendMessage($text){
        $this->bot('sendMessage', [
            'chat_id' => $this->chat_id,
            'text' => $text
        ]);
    }

    public function sendPhoto($photo){
        $this->bot('sendPhoto', [
            'chat_id' => $this->chat_id,
            'photo' => $photo
        ]);
    }
}

