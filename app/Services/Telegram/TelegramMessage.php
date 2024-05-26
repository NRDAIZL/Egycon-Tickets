<?php 

namespace App\Services\Telegram;

use App\Services\Telegram\TelegramMessage\ActionUrl;

class TelegramMessage
{
    /**
     * The subject of the message.
     *
     * @var string
     */
    public $subject;

    /**
     * The message's greeting.
     *
     * @var string
     */
    public $greeting;

    /**
     * The message's salutation.
     *
     * @var string
     */
    public $salutation;

    /**
     * The "intro" lines of the message.
     *
     * @var array
     */
    public $lines = [];

    /**
     * The action of the message.
     *
     * @var ActionUrl
     */
    public $action;



    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function greeting($greeting){
        $this->greeting = $greeting;
        return $this;
    }

    public function line($line){
        $this->lines[] = $line;
        return $this;
    }

    public function separator(){
        $this->lines[] = "---------------------------";
        return $this;
    }

    public function action(ActionUrl $actionUrl){
        $this->action = $actionUrl;
        return $this;
    }

    public function actionText($text){
        if(!$this->action){
            $this->action = new ActionUrl();
        }
        $this->action->text = $text;
        return $this;
    }

    public function actionUrl($url){
        if(!$this->action){
            $this->action = new ActionUrl();
        }
        $this->action->url = $url;
        return $this;
    }

    public function toString(){
        $message = "";
        if($this->greeting){
            $message .= $this->greeting . "\n";
        }
        if($this->subject){
            $message .= $this->subject . "\n";
        }
        if($this->lines){
            $message .= implode("\n", $this->lines) . "\n";
        }
        if($this->salutation){
            $message .= $this->salutation . "\n";
        }
        return $message;
    }

    public function __toString(){
        return $this->toString();
    }
}