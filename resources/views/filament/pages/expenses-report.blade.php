<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300">Periode</label>
                <select wire:model.live="period" class="w-full mt-1 rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                    <option value="all">Tout</option>
                    <option value="this_month">Ce mois</option>
                    <option value="last_month">Mois dernier</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300">Du</label>
                <input type="date" wire:model.live="fromDate" class="w-full mt-1 rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300">Au</label>
                <input type="date" wire:model.live="toDate" class="w-full mt-1 rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Total Depenses: {{ number_format($totalExpenses, 2) }} DZD</h2>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($summary as $row)
                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">{{ $row['label'] }}</div>
                            <div class="text-xl font-semibold text-gray-800 dark:text-gray-100">{{ number_format($row['amount'], 2) }} DZD</div>
                            <div class="text-xs text-gray-500">{{ number_format($row['percentage'], 1) }}%</div>
                        </div>
                        <x-filament::button size="sm" color="gray" wire:click="selectCategory('{{ $row['key'] }}')">
                            Voir details
                        </x-filament::button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if($selectedCategory && !empty($categoryDetails))
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ $categoryDetails['label'] }}</h3>
                    <p class="text-sm text-gray-500">Total: {{ number_format($categoryDetails['total'], 2) }} DZD</p>
                </div>
                <div class="text-sm text-gray-500">
                    Nombre de sorties: {{ $categoryDetails['count'] }} | Moyenne: {{ number_format($categoryDetails['average'], 2) }} DZD
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Montant</th>
                            <th class="px-4 py-2">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categoryDetails['items'] as $detail)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-4 py-2">{{ $detail['date'] }}</td>
                                <td class="px-4 py-2">{{ number_format($detail['amount'], 2) }}</td>
                                <td class="px-4 py-2">{{ $detail['notes'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>
