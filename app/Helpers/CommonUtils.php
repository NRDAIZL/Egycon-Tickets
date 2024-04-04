<?php

namespace App\Helpers;

class CommonUtils 
{
    public static function sum($carry = 0, $item)
    {
        $carry += $item;
        return $carry;
    }
    
}
