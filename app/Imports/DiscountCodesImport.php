<?php

namespace App\Imports;

use App\Models\TicketDiscountCode;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DiscountCodesImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        return new TicketDiscountCode([
            'code' => $collection["code"],
        ]);
    }
}
