<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    public function ticket()
    {
        return $this->hasMany(PostTicket::class)->withTrashed();
    }

    // post belongs to ticket type through ticket
    public function ticket_type()
    {
        return $this->belongsToMany(TicketType::class, 'post_tickets', 'post_id', 'ticket_type_id');
    }
    

    public function provider(){
        return $this->belongsTo(ExternalServiceProvider::class,'external_service_provider_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function promo_code()
    {
        return $this->belongsTo(PromoCode::class);
    }
    
}
