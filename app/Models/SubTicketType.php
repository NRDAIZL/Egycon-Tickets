<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTicketType extends Model
{
    use HasFactory;

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


}
