<?php

namespace App\Exports;

use App\Models\PromoCode;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PromoCodeExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;
    private $event_id;
    private $data;
    public function __construct($event_id, $data = null)
    {
        $this->event_id = $event_id;
        $this->data = $data;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        if($this->data){
            return $this->data;
        }
        return PromoCode::where('event_id',$this->event_id)->with('ticket_types')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Code',
            'Ticket Type',
            'Discount',
            'Max Uses',
            'Uses',
            'Is Active',
            'Event',
        ];
    }

    public function map($promoCode): array
    {
        return [
            $promoCode->id,
            $promoCode->code,
            $promoCode->ticket_types->implode('name', ', '), // implode ticket types
            $promoCode->discount,
            $promoCode->max_uses,
            $promoCode->uses,
            $promoCode->is_active,
            $promoCode->event->name,
        ];
    }

    
}
