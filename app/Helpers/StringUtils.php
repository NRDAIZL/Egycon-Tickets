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

    public static function generateRandomString($length = 10){
        return substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    public static function simplifyString(string $string): string{
        $string = strtolower($string);
        $string = preg_replace('/[^a-zA-Z0-9]/', '', $string);
        $string = preg_replace("/\s/",'', $string);
        return $string;
    }

    public static function toTitleCase(string $string): string{
        $pattern = '/([;:,-_.\/ X])/';
        $result = preg_replace($pattern, ' ', $string);
        $result = ucwords(strtolower($result));
        return $result;
    }

    public static function toSnakeCase(string $string): string{
        $string = preg_replace('/\s+/', '_', $string);
        $string = strtolower($string);
        return $string;
    }

    public static function getEnvValue(string $fileName, string $key){
        $file = file_get_contents($fileName);
        $lines = explode("\n", $file);
        foreach($lines as $line){
            $line = trim($line);
            if(strpos($line, $key) === 0){
                $value = explode("=", $line)[1];
                return $value;
            }
        }
        return null;
    }

}