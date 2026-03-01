<?php

namespace App\Filament\Pages;

use App\Models\FinancialTransaction;
use App\Services\TreasuryEngine;
use Carbon\Carbon;
use Filament\Pages\Page;

class ExpensesReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Depenses par categorie';

    protected static ?string $navigationGroup = 'Rapports';

    protected static ?string $title = 'Rapports des Depenses';

    protected static string $view = 'filament.pages.expenses-report';

    public string $period = 'this_month';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public array $summary = [];

    public float $totalExpenses = 0;

    public ?string $selectedCategory = null;

    public array $categoryDetails = [];

    public function mount(): void
    {
        $this->setPeriodDates('this_month');
        $this->calculateData();
    }

    public function updatedPeriod(string $value): void
    {
        $this->setPeriodDates($value);
        $this->calculateData();
    }

    public function updatedFromDate(): void
    {
        $this->calculateData();
    }

    public function updatedToDate(): void
    {
        $this->calculateData();
    }

    public function selectCategory(string $category): void
    {
        $this->selectedCategory = $category;
        $this->calculateData();
    }

    private function setPeriodDates(string $period): void
    {
        $now = now();

        if ($period === 'last_month') {
            $start = $now->copy()->subMonthNoOverflow()->startOfMonth();
            $end = $start->copy()->endOfMonth();
        } else {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
        }

        $this->fromDate = $start->toDateString();
        $this->toDate = $end->toDateString();
    }

    private function calculateData(): void
    {
        if (! $this->fromDate || ! $this->toDate) {
            return;
        }

        $from = Carbon::parse($this->fromDate)->startOfDay();
        $to = Carbon::parse($this->toDate)->endOfDay();

        $transactions = FinancialTransaction::query()
            ->where('type', TreasuryEngine::TYPE_EXPENSE)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $this->totalExpenses = (float) $transactions->sum('amount');

        $this->summary = [];
        foreach (FinancialTransaction::CATEGORY_OPTIONS as $key => $label) {
            $amount = (float) $transactions->where('categorie', $key)->sum('amount');
            $percentage = $this->totalExpenses > 0 ? ($amount / $this->totalExpenses) * 100 : 0;

            $this->summary[] = [
                'key' => $key,
                'label' => $label,
                'amount' => $amount,
                'percentage' => $percentage,
            ];
        }

        $this->categoryDetails = [];
        if ($this->selectedCategory) {
            $details = $transactions->where('categorie', $this->selectedCategory);
            $this->categoryDetails = [
                'label' => FinancialTransaction::CATEGORY_OPTIONS[$this->selectedCategory] ?? $this->selectedCategory,
                'total' => (float) $details->sum('amount'),
                'count' => $details->count(),
                'average' => $details->count() > 0 ? (float) $details->avg('amount') : 0,
                'items' => $details->map(function (FinancialTransaction $transaction) {
                    return [
                        'date' => $transaction->created_at?->format('d/m/Y'),
                        'amount' => $transaction->amount,
                        'notes' => $transaction->notes,
                    ];
                })->values()->all(),
            ];
        }
    }
}
