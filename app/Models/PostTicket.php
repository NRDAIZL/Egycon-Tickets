<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PostTicket extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes;

    public function ticket_type(){
        return $this->belongsTo(TicketType::class)->withTrashed();
    }

    public function discount_code(){
        return $this->belongsTo(TicketDiscountCode::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function isAvailable($date){
        $event_day = $this->ticket_type->event_days->where('date', $date)->first();
        if($event_day){
            return true;
        }
        return false;
    }

    public function sub_ticket_type(){
        return $this->belongsTo(SubTicketType::class);
    }
}
