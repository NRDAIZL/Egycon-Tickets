<?php 
namespace App\Exceptions;

class InvalidPromoCodeException extends \Exception
{
    protected $promoCode;
    public function __construct($message = "", $promoCode, $code = 0, \Throwable $previous = null)
    {
        $this->promoCode = $promoCode;
        parent::__construct($message, $code, $previous);
    }

    public function getPromoCode(){
        return $this->promoCode;
    }
}
