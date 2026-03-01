<?php

namespace App\Filament\Widgets;

use App\Models\FinancialTransaction;
use App\Services\TreasuryEngine;
use Filament\Widgets\Widget;

class MonthlyExpensesWidget extends Widget
{
    protected static string $view = 'filament.widgets.monthly-expenses-widget';

    protected function getViewData(): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $transactions = FinancialTransaction::where('type', TreasuryEngine::TYPE_EXPENSE)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $total = (float) $transactions->sum('amount');
        $publicite = (float) $transactions->where('categorie', 'publicite')->sum('amount');
        $personnel = (float) $transactions->whereIn('categorie', ['depense_vous', 'depense_partner'])->sum('amount');
        $dollars = (float) $transactions->where('categorie', 'achat_dollars')->sum('amount');

        return [
            'total' => $total,
            'publicite' => $publicite,
            'personnel' => $personnel,
            'dollars' => $dollars,
            'publiciteRate' => $total > 0 ? ($publicite / $total) * 100 : 0,
            'personnelRate' => $total > 0 ? ($personnel / $total) * 100 : 0,
            'dollarsRate' => $total > 0 ? ($dollars / $total) * 100 : 0,
        ];
    }
}
