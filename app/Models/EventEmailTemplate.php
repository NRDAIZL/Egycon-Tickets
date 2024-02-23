<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EventEmailTemplate extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    protected $fillable = [
        'subject',
        'body',
        'type',
        'event_id',
    ];
}
