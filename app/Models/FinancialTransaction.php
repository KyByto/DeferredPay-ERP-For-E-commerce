<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    public const CATEGORY_OPTIONS = [
        'boites' => 'Boites de livraison',
        'depense_aramis' => 'Depense Aramis',
        'depense_marouane' => 'Depense Marouane',
        'ajustement_societe' => 'Ajustement societe de livraison',
        'achat_dollars' => 'Achat de dollars',
        'autres' => 'Autres frais',
    ];

    protected $fillable = [
        'type',
        'amount',
        'source_type',
        'source_id',
        'destination_type',
        'destination_id',
        'description',
        'categorie',
        'notes',
    ];
}
