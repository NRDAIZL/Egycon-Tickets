<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'location',
        'logo',
        'banner',
        'google_maps_url',
        'registration_start',
        'registration_end',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function ticket_types()
    {
        return $this->hasMany(TicketType::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function getPostsCountAttribute()
    {
        return $this->posts->count();
    }

    public function getUsersAttribute()
    {
        return $this->users()->get();
    }
    public function getAdminNamesAttribute()
    {
        return $this->getUsersAttribute()->pluck('name')->toArray();
    }
    public function getTicketTypesAttribute()
    {
        return $this->ticket_types()->get();
    }

    public function getTicketTypesCountAttribute()
    {
        return $this->ticket_types->count();
    }

    public function event_days()
    {
        return $this->hasMany(EventDay::class);
    }

    // calculate duration of event in days
    public function getDurationAttribute()
    {
        $start_date = $this->event_days->min('date');
        $end_date = $this->event_days->max('date');
        $start_date = new \DateTime($start_date);
        $end_date = new \DateTime($end_date);
        $interval = $start_date->diff($end_date);
        if($interval->days == 0) {
            // get hours and minutes
            $start_time = $this->event_days->min('start_time');
            $end_time = $this->event_days->max('end_time');
            if($start_time == null || $end_time == null){
                return null;
            }
            $start_time = new \DateTime($start_time);
            $end_time = new \DateTime($end_time);
            $interval = $start_time->diff($end_time);
            $hours = $interval->h;
            return $hours. Str::plural(' hour', $hours);
        }
        return $interval->days + 1 . Str::plural(' day', $interval->days + 1);
    }

    public function getStartDateAttribute()
    {
        return $this->event_days->min('date');
    }

    // themes
    public function themes()
    {
        return $this->hasMany(EventTheme::class);
    }

    public function invitations()
    {
        return $this->hasMany(UserInvitation::class);
    }

    public function payment_methods()
    {
        return $this->hasMany(EventPaymentMethod::class);
    }

    public function questions()
    {
        return $this->hasMany(EventQuestion::class);
    }

    public function promo_codes()
    {
        return $this->hasMany(PromoCode::class);
    }
}
