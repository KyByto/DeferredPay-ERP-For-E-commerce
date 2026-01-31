<?php

namespace App\Filament\Resources\FinancialTransactionResource\Pages;

use App\Filament\Resources\FinancialTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Services\TreasuryEngine;
use Filament\Notifications\Notification;

class ListFinancialTransactions extends ListRecords
{
    protected static string $resource = FinancialTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('releve_societe')
                ->label('Faire un Relevé (Société -> Caisse)')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\TextInput::make('amount')
                        ->label('Montant à relever')
                        ->numeric()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $engine = new TreasuryEngine();
                    $societeBalance = $engine->getSocieteBalance();
                    
                    // Allow override or strictly block?
                    // "Le système doit interdire un paiement ou un relevé si le montant est supérieur" (FR16)
                    // Strict block.
                    if ($data['amount'] > $societeBalance) {
                        Notification::make()
                            ->title('Solde Société Insuffisant')
                            ->body("La société n'a que " . number_format($societeBalance, 2) . " DZD.")
                            ->danger()
                            ->send();
                        return;
                    }

                    $engine->registerTransfer(
                        TreasuryEngine::ACCOUNT_SOCIETE,
                        TreasuryEngine::ACCOUNT_CAISSE,
                        $data['amount']
                    );

                    Notification::make()
                        ->title('Relevé effectué')
                        ->success()
                        ->send();
                }),
        ];
    }
}