<?php

namespace App\Helpers;
use App\Exceptions\WrongValueException;
use stdClass;

class HttpHelper
{
    public static function redirectError($message){
        return redirect()->back()->with('status-failure', $message);
    }
}