<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use App\Services\TreasuryEngine;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $engine = new TreasuryEngine;

        $transaction = $engine->addExpense(
            $data['categorie'],
            $data['amount'],
            $data['notes'] ?? ''
        );

        if (! empty($data['created_at'])) {
            $transaction->update(['created_at' => $data['created_at']]);
        }

        return $transaction;
    }
}
