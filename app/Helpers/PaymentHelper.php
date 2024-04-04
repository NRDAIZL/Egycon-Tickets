<?php

namespace App\Helpers;
use App\Exceptions\WrongValueException;
use stdClass;

class PaymentHelper
{
    protected static $payment_methods = [
        "vodafonecash" => [
            "receipt" => true,
            "automatic" => false,
        ],
        "instapay" => [
            "receipt" => true,
            "automatic" => false,
        ],
        "creditcard" => [
            "receipt" => false,
            "automatic" => true,
        ],
        "reservation" => [
            "receipt" => false,
            "automatic" => true,
        ],
    ];
    
    public static function paymentReceiptRequired($payment_method) : bool {
        if (empty(self::$payment_methods[StringUtils::simplifyString($payment_method)])) {
            throw new WrongValueException("Payment method: $payment_method is not defined");
        }
        return self::$payment_methods[StringUtils::simplifyString($payment_method)]["receipt"];
    }

    public static function buildKashierData($total, $email, $full_name, $phone_number, $order_id, $order_reference, $event_id) : stdClass{
        $data = new stdClass();
        $data->amount = $total;
        $data->user_first_name = explode(' ', $full_name)[0];
        $data->user_last_name = explode(' ', $full_name)[1];
        $data->user_email = $email;
        $data->user_phone = $phone_number;
        $data->order_id = $order_id;
        $data->currency = "EGP";
        $data->order_reference_id = $order_reference;
        $data->event_id = $event_id;
        return $data;
    }
}
