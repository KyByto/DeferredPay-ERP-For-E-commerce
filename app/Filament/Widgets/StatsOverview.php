<?php

namespace App\Filament\Widgets;

use App\Models\Supplier;
use App\Services\TreasuryEngine;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    // Auto-refresh every 10s
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $engine = new TreasuryEngine();
        
        $cashBalance = $engine->getCashBalance();
        $societeBalance = $engine->getSocieteBalance();
        
        // Calculate total debt across all suppliers
        // Efficient way: Sum of all supplier balances where balance > 0
        // Or using TreasuryEngine logic if we had a global debt account.
        // Let's iterate suppliers for accuracy based on the logic we put in Supplier model.
        $totalDebt = Supplier::all()->sum(function ($supplier) {
            return $supplier->balance; // Debt - Payments
        });

        return [
            Stat::make('Caisse (Cash)', number_format($cashBalance, 2) . ' DZD')
                ->description('Argent physique disponible')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($cashBalance >= 0 ? 'success' : 'danger'),

            Stat::make('Chez Livreur (Société)', number_format($societeBalance, 2) . ' DZD')
                ->description('À relever')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),

            Stat::make('Dettes Fournisseurs', number_format($totalDebt, 2) . ' DZD')
                ->description('Reste à payer')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}