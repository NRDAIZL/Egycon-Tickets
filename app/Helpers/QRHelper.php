<?php

namespace App\Helpers;

use App\Models\Post;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QRHelper 
{
    protected $qrOptions;
    protected $qrImageFormat = "jpg";
    protected $qrImagePath = "images/qrcodes";
    public function __construct(){
        $this->qrOptions = new QROptions([
            'version'    => 5,
            'outputType' => QRCode::OUTPUT_IMAGE_JPG,
            'eccLevel'   => QRCode::ECC_L,
            'imageTransparent' => false,
            'imagickFormat' => 'jpg',
            'imageTransparencyBG' => [255, 255, 255],
        ]);
    }

    public function generate(string $code = null) : string {
        if(empty($code)){
            $code = self::generateRandomQrCode(6);
        }
        $qrcode = new QRCode($this->qrOptions);
        $qrcode->render($code, public_path("{$this->qrImagePath}/{$code}.{$this->qrImageFormat}"));
        return $code;
    }

    public static function generateRandomQrCode($length = 10){
        $code = StringUtils::generateRandomString($length);
        $code_exists = Post::where('code',$code)->first();
        while($code_exists){
            $code = StringUtils::generateRandomString($length);
            $code_exists = Post::where('code',$code)->first();
        }
        return $code;
    }
    
}
