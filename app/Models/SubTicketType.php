<?php

namespace App\Models;

use App\Models\TicketType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class SubTicketType extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        "name",
        "description",
        "price",
        "ticket_type_id",
        "is_active"
    ];

    public function ticket_type(){
        return $this->belongsTo(TicketType::class);
    }

    public function post_tickets(){
        return $this->hasMany(PostTicket::class);
    }


}
