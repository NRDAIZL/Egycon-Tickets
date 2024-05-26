<?php

namespace App\Helpers;
use App\Exceptions\WrongValueException;
use Illuminate\Support\Facades\Log;
use stdClass;

class HttpHelper
{
    const DEFAULT_TESTING_URL = "https://www.gamerslegacy.net";
    // constructor

    public static function redirectError($message){
        return redirect()->back()->with('status-failure', $message);
    }

    // public static function getSafeRoute($name, $parameters, $default = null){
    //     if(env('APP_ENV') == 'local'){
    //         return $default ?? self::DEFAULT_TESTING_URL;
    //     }
    //     return route($name, $parameters);
    // }
}