<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PaymentMethod extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'logo'
    ];
}
