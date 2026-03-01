<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300">Periode</label>
                <select wire:model="period" class="w-full mt-1 rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                    <option value="this_month">Ce mois</option>
                    <option value="last_month">Mois dernier</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300">Du</label>
                <input type="date" wire:model="fromDate" class="w-full mt-1 rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300">Au</label>
                <input type="date" wire:model="toDate" class="w-full mt-1 rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Commandes</h3>
            <div class="text-sm text-gray-600">Total: {{ $report['total_orders'] ?? 0 }} commandes</div>
            <div class="mt-2 text-sm">Shopify: {{ $report['shopify_orders'] ?? 0 }}</div>
            <div class="text-sm">Messages: {{ $report['messages_orders'] ?? 0 }}</div>
            <div class="mt-3 text-sm">Livrees: {{ $report['delivered_orders'] ?? 0 }}</div>
            <div class="text-sm">Retournees: {{ $report['returned_orders'] ?? 0 }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Produits</h3>
            <div class="text-sm text-gray-600">Total: {{ $report['total_units'] ?? 0 }} unites</div>
            <div class="mt-2 text-sm">Retournes: {{ $report['returned_units'] ?? 0 }} unites</div>
            <div class="text-sm">Stock retours actuel: {{ $report['current_stock'] ?? 0 }} unites</div>
            <div class="text-sm">Revendus depuis stock: {{ $report['resold_units'] ?? 0 }} unites</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Financier</h3>
            <div class="text-sm text-gray-600">CA Brut: {{ number_format($report['gross_revenue'] ?? 0, 2) }} DZD</div>
            <div class="mt-2 text-sm">Valeur retours: -{{ number_format($report['returns_value'] ?? 0, 2) }} DZD</div>
            <div class="text-sm">CA Net: {{ number_format($report['net_revenue'] ?? 0, 2) }} DZD</div>
            <div class="mt-2 text-sm">Depenses totales: -{{ number_format($report['total_expenses'] ?? 0, 2) }} DZD</div>
            <div class="text-sm">Profit Net: {{ number_format($report['profit'] ?? 0, 2) }} DZD</div>
            <div class="text-sm">Marge: {{ number_format($report['margin'] ?? 0, 1) }}%</div>
        </div>
    </div>
</x-filament-panels::page>
