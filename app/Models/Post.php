<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public function ticket_type(){
        return $this->belongsTo(TicketType::class);
    }

    public function ticket()
    {
        return $this->hasMany(PostTicket::class);
    }
    
    public function provider(){
        return $this->belongsTo(ExternalServiceProvider::class,'external_service_provider_id');
    }

    
}
