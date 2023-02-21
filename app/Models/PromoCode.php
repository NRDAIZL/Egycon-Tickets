<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
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
    
}
