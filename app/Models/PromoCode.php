<?php

namespace App\Models;

use App\Exceptions\InvalidPromoCodeException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PromoCode extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory;

    protected $fillable = [
        'code',
        'ticket_type_id',
        'discount',
        'max_uses',
        'uses',
        'is_active',
        'event_id',
    ];

    public function ticket_types()
    {
        return $this->belongsToMany(TicketType::class, 'promo_code_ticket_type', 'promo_code_id', 'ticket_type_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public static function getAndValidateEventCode($code, $event_id){
        $code = self::where('code',$code)->where('is_active', 1)->where('event_id',$event_id)->first();
        if(!$code){
            throw new InvalidPromoCodeException("Invalid code", $code);
        }
        if($code->is_active != 1 || $code->max_uses <= $code->uses){
            throw new InvalidPromoCodeException("This code has reached the maximum number of uses", $code);
        }
        return $code;
    }
}
