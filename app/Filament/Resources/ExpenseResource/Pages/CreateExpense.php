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
        $engine = new TreasuryEngine();
        
        if ($data['type'] === TreasuryEngine::TYPE_EXPENSE) {
            // Using 'Autre' as category default if not provided
            return $engine->addExpense('Autre', $data['amount'], $data['description']);
        }
        
        if ($data['type'] === TreasuryEngine::TYPE_INCOME) {
            // Using 'Autre' as source default
            return $engine->addIncome('Autre', $data['amount'], $data['description']);
        }

        // Fallback (should not happen given form options)
        return parent::handleRecordCreation($data);
    }
}