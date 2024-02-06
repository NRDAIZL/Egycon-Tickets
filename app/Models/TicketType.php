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
        'is_active'
    ];

    protected $hidden = ['pivot'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function sub_ticket_types(){
        return $this->hasMany(SubTicketType::class);
    }
}
