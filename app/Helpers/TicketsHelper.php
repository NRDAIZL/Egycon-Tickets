<?php

namespace App\Helpers;

use App\Exceptions\InvalidRequestException;
use App\Exceptions\RequiresRequestException;
use App\Models\SubTicketType;
use Illuminate\Http\Request;
use stdClass;

class TicketsHelper
{
    private $request;
    private $event;

    // constructor
    public function __construct(Request $request = null, $event = null)
    {
        $this->request = $request;
        $this->event = $event;
    }

    private function requiresRequest()
    {
        if (!$this->request || !($this->request instanceof Request)) {
            throw new RequiresRequestException("Method requires a valid Request object");
        }
    }

    public function countAndValidateReservations($ticketTypes)
    {
        $this->requiresRequest();
        $totalReservationTickets = 0;
        $i = 0;
        foreach ($ticketTypes as $ticketType) {
            if ($ticketType->type == "reservation" && ($this->request->quantity[$i] ?? 0) > 0) {
                $totalReservationTickets += $this->request->quantity[$i];
                if ($totalReservationTickets > 5)
                    throw new InvalidRequestException("You cannot select more than 5 reservation tickets");
            } else if (($this->request->quantity[$i] ?? 0) > 0  && $totalReservationTickets > 0) {
                throw new InvalidRequestException("You cannot select reservation tickets with other tickets");
            }
            $i++;
        }
        return $totalReservationTickets;
    }

    public function getSubTicketsSelected($ticketTypes)
    {
        $this->requiresRequest();
        $i = 0;
        $selected_sub_ticket_types = [];
        foreach ($ticketTypes as $ticket_type) {
            if ($ticket_type->sub_ticket_types()->count() > 0 && ($this->request->quantity[$i] ?? 0) > 0) {
                $selected_sub_ticket_types[$ticket_type->id] = [];
                foreach ($this->request["sub_ticket_" . $ticket_type->id] as $sub_ticket_type) {
                    $sub_ticket_type = SubTicketType::find($sub_ticket_type);
                    if ($sub_ticket_type == null || empty($sub_ticket_type)) {
                        throw new InvalidRequestException("Invalid sub-ticket selection for ". $ticket_type->name);
                    }
                    for ($j = 0; $j < $ticket_type->person; $j++) {
                        $selected_sub_ticket_types[$ticket_type->id][] = $sub_ticket_type->id;
                    }
                }
            }
            $i++;
        }
        return $selected_sub_ticket_types;
    }
}
