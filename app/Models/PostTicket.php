<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostTicket extends Model
{
    use HasFactory, SoftDeletes;

    public function ticket_type(){
        return $this->belongsTo(TicketType::class);
    }

    public function discount_code(){
        return $this->belongsTo(TicketDiscountCode::class);
    }
    
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
