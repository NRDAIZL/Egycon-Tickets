<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserInvitation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    protected $fillable = [
        'email',
        'role_id',
        'invited_by',
        'token',
        'expires_at',
        'accepted_at',
        'event_id'
    ];


    public function invitedBy(){
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopePending($query){
        return $query->whereNull('accepted_at');
    }

    public function scopeAccepted($query){
        return $query->whereNotNull('accepted_at');
    }

    public function scopeExpired($query){
        return $query->whereNotNull('expires_at')->where('expires_at', '<', now());
    }

    public function scopeNotExpired($query){
        return $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
    }

    public function role(){
        return $this->belongsTo(Role::class);
    }

    public function event(){
        return $this->belongsTo(Event::class);
    }

}
