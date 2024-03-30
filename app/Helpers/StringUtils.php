<?php

namespace App\Helpers;

class StringUtils {

    const DEFAULT_SEARCH_PREFIX = "<span class='bg-yellow-100'>";
    const DEFAULT_SEARCH_SUFFIX = "</span>";
    // Search for word in string case insensitive and replace with wrapper using regex
    public static function highlight($string, $word, $prefix = null, $suffix = null){
        if($prefix == null){
            return self::highlight($string, $word, self::DEFAULT_SEARCH_PREFIX, $suffix);
        }
        if($suffix == null){
            return self::highlight($string, $word, $prefix, self::DEFAULT_SEARCH_SUFFIX);
        }
        $word = strval($word);
        $string = strval($string);
        $regex = "/(?i)($word)/u";
        return preg_replace($regex, $prefix."$1".$suffix, $string);
    }

    public static function wrapWithParentheses ($string){
        return "($string)";
    }

}