<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    protected $fillable = [
        'type',
        'amount',
        'source_type',
        'source_id',
        'destination_type',
        'destination_id',
        'description',
    ];
}
