<?php

namespace App\Imports;

use App\Models\Post;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PostImport implements ToCollection, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $row)
    {
        return new Post([
            'order_id'=> $row["order_id"],
            'name' => $row["name"],
            'phone' => $row["phone"],
            'email' => $row["email"],
            'quantity' => $row["qty"],
            'ticket_type' => $row["ticket type"],
            'payment_method' => $row["payment method"],
            'notes' => $row["nots"],
        ]);
    }
}
