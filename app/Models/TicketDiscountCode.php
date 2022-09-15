<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketDiscountCode extends Model
{
    use HasFactory, SoftDeletes;

    public function ticket()
    {
        return $this->hasOne(PostTicket::class, 'discount_code_id');
    }
}
