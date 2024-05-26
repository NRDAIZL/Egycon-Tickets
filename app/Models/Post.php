<?php

namespace App\Models;

use App\Helpers\StringUtils;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Post extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes;

    public function ticket()
    {
        return $this->hasMany(PostTicket::class)->withTrashed();
    }

    // post belongs to ticket type through ticket
    public function ticket_type()
    {
        return $this->belongsToMany(TicketType::class, 'post_tickets', 'post_id', 'ticket_type_id');
    }


    public function provider()
    {
        return $this->belongsTo(ExternalServiceProvider::class, 'external_service_provider_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function promo_code()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function getPromoCode()
    {
        $promo_code = $this->promo_code;
        if ($promo_code) {
            return $promo_code->code;
        }
        return null;
    }

    public function getTicketsArray()
    {
        $similar = [];
        $similar_person = [];

        $tickets = [];
        foreach ($this->ticket as $ticket) {
            if (!isset($ticket->ticket_type)) {
                $tickets[] = "N/A";
                continue;
            }
            if (isset($ticket->sub_ticket_type) && !isset($ticket->ticket_type->name_chaned)) {
                $ticket->ticket_type->name = $ticket->ticket_type->name . " " . StringUtils::wrapWithParentheses($ticket->sub_ticket_type->name);
                $ticket->ticket_type->name_chaned = true;
            }
            if (!isset($similar[$ticket->ticket_type->name])) {

                $similar[$ticket->ticket_type->name] = 1;

                $similar_person[$ticket->ticket_type->name] = $ticket->ticket_type->person;
            } else {
                $similar[$ticket->ticket_type->name]++;
            }
        }

        foreach ($similar as $key => $value) {
            $tickets[] = $value / $similar_person[$key] . " " . $key;
        }
        return $tickets;
    }
}
