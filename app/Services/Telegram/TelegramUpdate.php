<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Log;

class TelegramUpdate{
    private $update;
    public $type;
    public $data;

    public function __construct($update){
        $this->update = $update;
        $this->data = @$update->callback_query->data;
        $this->type = @$update->chat->type;
        $type2 = @$update->callback_query->message->chat->type;
        $this->type = $this->type ?? $type2;
        Log::channel('telegram')->info('Message Received: '. json_encode($this->update));
    }

    public function getChatId(){
        return @$this->update->message->chat->id ?? @$this->update->callback_query->message->chat->id;
    }

    public function getText()
    {
        return @$this->update->message->text;
    }
}