<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventTheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'theme_color',
        'registration_form_background_color',
        'registration_page_background_image',
        'registration_page_header_image',
        'is_active'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
