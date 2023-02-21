<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'name',
        'price',
        'person',
        'type',
        'is_active',
        'scan_type',
        'is_disabled',
        'is_visible',
    ];

    protected $hidden = ['pivot'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function event_days()
    {
        return $this->belongsToMany(EventDay::class, 'ticket_type_event_day', 'ticket_type_id', 'event_day_id');
    }

    public function promoCodes()
    {
        return $this->belongsToMany(PromoCode::class, 'promo_code_ticket_type', 'ticket_type_id', 'promo_code_id');
    }
}
