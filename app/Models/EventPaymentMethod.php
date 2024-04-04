<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EventPaymentMethod extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public static function getAndMapMethods($event_id){
        $payment_methods = EventPaymentMethod::where('event_id', $event_id)->where('is_active', true)->get();
        $global_payment_methods = PaymentMethod::all();
        $payment_methods = $payment_methods->map(function ($payment_method) use ($global_payment_methods) {
            $global_payment_method = $global_payment_methods->where('id', $payment_method->payment_method_id)->first();
            if ($payment_method->name == null)
                $payment_method->name = $global_payment_method->name;
            $payment_method->logo = $global_payment_method->logo;
            return $payment_method;
        });
        return $payment_methods;
    }
}
