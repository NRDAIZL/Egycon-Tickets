<?php

namespace App\Services\Telegram\TelegramMessage;

class ActionUrl{
    public $text;
    public $url;
    
    public function toString(){
        return "[$this->url]({$this->text})";
    }

    public function toArray(){
        return [
            'text' => $this->text,
            'url' => $this->url
        ];
    }
}