<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    public const CATEGORY_OPTIONS = [
        'publicite' => 'Publicite (Meta Ads)',
        'boites' => 'Boites de livraison',
        'depense_vous' => 'Depense personnelle (Vous)',
        'depense_partner' => 'Depense personnelle (Partenaire)',
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
