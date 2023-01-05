<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    public function ticket_type(){
        return $this->belongsTo(TicketType::class)->withTrashed();
    }

    public function ticket()
    {
        return $this->hasMany(PostTicket::class)->withTrashed();
    }
    
    public function provider(){
        return $this->belongsTo(ExternalServiceProvider::class,'external_service_provider_id');
    }

    
}
