<?php

namespace App\Filament\Pages;

use App\Models\FinancialTransaction;
use App\Models\Order;
use App\Services\TreasuryEngine;
use Carbon\Carbon;
use Filament\Pages\Page;

class SalesReturnsReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationLabel = 'Ventes vs Retours';

    protected static ?string $navigationGroup = 'Rapports';

    protected static ?string $title = 'Rapport Ventes & Retours';

    protected static string $view = 'filament.pages.sales-returns-report';

    public string $period = 'all';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public array $report = [];

    public function mount(): void
    {
        $this->setPeriodDates('all');
        $this->calculateReport();
    }

    public function updatedPeriod(string $value): void
    {
        $this->setPeriodDates($value);
        $this->calculateReport();
    }

    public function updatedFromDate(): void
    {
        $this->calculateReport();
    }

    public function updatedToDate(): void
    {
        $this->calculateReport();
    }

    private function setPeriodDates(string $period): void
    {
        $now = now();

        if ($period === 'last_month') {
            $start = $now->copy()->subMonthNoOverflow()->startOfMonth();
            $end = $start->copy()->endOfMonth();
        } elseif ($period === 'this_month') {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
        } else {
            // "all" ou toute valeur inconnue : toute la base
            $minDate = Order::min('order_date');
            $start = $minDate ? Carbon::parse($minDate)->startOfDay() : $now->copy()->subYear()->startOfDay();
            $end = $now->copy()->endOfDay();
        }

        $this->fromDate = $start->toDateString();
        $this->toDate = $end->toDateString();
    }

    private function calculateReport(): void
    {
        if (! $this->fromDate || ! $this->toDate) {
            return;
        }

        $from = Carbon::parse($this->fromDate)->startOfDay();
        $to = Carbon::parse($this->toDate)->endOfDay();

        $orders = Order::query()->whereBetween('order_date', [$from, $to]);
        $totalOrders = (clone $orders)->count();
        $shopifyOrders = (clone $orders)->where('source', 'shopify')->count();
        $messagesOrders = (clone $orders)->where('source', 'messages')->count();
        $deliveredOrders = (clone $orders)->where('status', 'delivered')->count();
        $returnedOrders = (clone $orders)->where('status', 'returned')->count();

        $totalUnits = 0;
        $orders->get()->each(function (Order $order) use (&$totalUnits) {
            $items = $order->items ?? [];
            foreach ($items as $item) {
                $totalUnits += (int) ($item['quantity'] ?? 0);
            }
        });

        // Count returned units and resold units from returned orders in the period
        $returnedOrdersData = (clone $orders)->where('status', 'returned')->get();
        $returnedUnits = 0;
        $resoldUnits = 0;
        $currentStock = 0;

        foreach ($returnedOrdersData as $order) {
            $items = $order->items ?? [];
            $returnedSold = $order->returned_sold ?? [];

            foreach ($items as $index => $item) {
                $qty = (int) ($item['quantity'] ?? 0);
                $sold = (int) ($returnedSold[$index]['sold'] ?? 0);
                $removed = (int) ($returnedSold[$index]['removed'] ?? 0);

                $returnedUnits += $qty;
                $resoldUnits += $sold;
                $currentStock += max(0, $qty - $sold - $removed);
            }
        }

        // Also compute currentStock across ALL returned orders (not just the period)
        $allReturnedOrders = Order::where('status', 'returned')->get();
        $currentStock = 0;
        foreach ($allReturnedOrders as $order) {
            $items = $order->items ?? [];
            $returnedSold = $order->returned_sold ?? [];

            foreach ($items as $index => $item) {
                $qty = (int) ($item['quantity'] ?? 0);
                $sold = (int) ($returnedSold[$index]['sold'] ?? 0);
                $removed = (int) ($returnedSold[$index]['removed'] ?? 0);
                $currentStock += max(0, $qty - $sold - $removed);
            }
        }

        $grossRevenue = (clone $orders)->where('status', 'delivered')->sum('total_price');
        $returnsValue = (clone $orders)->where('status', 'returned')->sum('total_price');
        $netRevenue = $grossRevenue - $returnsValue;

        $totalExpenses = FinancialTransaction::where('type', TreasuryEngine::TYPE_EXPENSE)
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        $profit = $netRevenue - $totalExpenses;
        $margin = $netRevenue > 0 ? ($profit / $netRevenue) * 100 : 0;

        $this->report = [
            'total_orders' => $totalOrders,
            'shopify_orders' => $shopifyOrders,
            'messages_orders' => $messagesOrders,
            'delivered_orders' => $deliveredOrders,
            'returned_orders' => $returnedOrders,
            'total_units' => $totalUnits,
            'returned_units' => $returnedUnits,
            'current_stock' => $currentStock,
            'resold_units' => $resoldUnits,
            'gross_revenue' => $grossRevenue,
            'returns_value' => $returnsValue,
            'net_revenue' => $netRevenue,
            'total_expenses' => $totalExpenses,
            'profit' => $profit,
            'margin' => $margin,
        ];
    }
}
