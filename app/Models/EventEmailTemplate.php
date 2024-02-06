<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventEmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'body',
        'type',
        'event_id',
    ];
}
