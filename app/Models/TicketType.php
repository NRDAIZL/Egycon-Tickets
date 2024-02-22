<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class TicketType extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

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

    public function sub_ticket_types(){
        return $this->hasMany(SubTicketType::class);
    }
    public function event_days()
    {
        return $this->belongsToMany(EventDay::class, 'ticket_type_event_day', 'ticket_type_id', 'event_day_id');
    }

    public function promoCodes()
    {
        return $this->belongsToMany(PromoCode::class, 'promo_code_ticket_type', 'ticket_type_id', 'promo_code_id');
    }

    public function tickets()
    {
        return $this->hasMany(PostTicket::class);
    }

    public function posts(){
        return $this->hasManyThrough(Post::class, PostTicket::class, 'ticket_type_id', 'id', 'id', 'post_id');
    }

    public function get_scans_count(){
        $scans = PostTicket::where('ticket_type_id',$this->id)->where('scanned_at',"!=",NULL)->count();
        return $scans;
    }
}
