<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\MorphMany;

class Supplier extends Model
{
    protected $fillable = ['name', 'contact_info'];

    public function debts(): MorphMany
    {
        return $this->morphMany(FinancialTransaction::class, 'source')
            ->where('type', 'debt');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(FinancialTransaction::class, 'destination')
            ->where('type', 'payment');
    }

    public function getBalanceAttribute(): float
    {
        // Debt (we owe them) - Payments (we paid them)
        // If Debt is positive, we owe money.
        $totalDebt = $this->debts()->sum('amount');
        $totalPaid = $this->payments()->sum('amount');

        return $totalDebt - $totalPaid;
    }
}
