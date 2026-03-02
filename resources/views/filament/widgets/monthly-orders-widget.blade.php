<x-filament::widget>
    <x-filament::card>
        <div class="flex items-center justify-between mb-3">
            <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">Statistiques Produits Livres</div>
        </div>

        <div class="grid grid-cols-2 gap-2 mb-3">
            <div>
                <label class="block text-xs text-gray-500 dark:text-gray-400">Du</label>
                <input type="date" wire:model.live="fromDate" value="{{ $fromDate }}"
                    class="w-full mt-1 text-xs rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div>
                <label class="block text-xs text-gray-500 dark:text-gray-400">Au</label>
                <input type="date" wire:model.live="toDate" value="{{ $toDate }}"
                    class="w-full mt-1 text-xs rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600">
            </div>
        </div>

        <div class="space-y-1 text-sm border-t dark:border-gray-700 pt-3">
            <div class="flex justify-between">
                <span class="text-gray-500">Shopify livres</span>
                <span class="font-semibold text-blue-600">{{ $shopifyCount }} produits</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Messages livres</span>
                <span class="font-semibold text-purple-600">{{ $messagesCount }} produits</span>
            </div>
            <div class="flex justify-between border-t dark:border-gray-700 pt-1 mt-1">
                <span class="font-semibold text-gray-700 dark:text-gray-200">Total livres</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ $totalCount }} produits</span>
            </div>
        </div>

        <div class="space-y-1 text-sm border-t dark:border-gray-700 pt-3 mt-3">
            <div class="flex justify-between">
                <span class="text-gray-500">CA</span>
                <span class="font-semibold text-green-600">{{ number_format($grossRevenue, 2) }} DZD</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Retours (produits)</span>
                <span class="font-semibold text-red-600">{{ $returnedCount }} produits
                    @if($returnsRate > 0)
                        <span class="text-xs text-red-400">({{ number_format($returnsRate, 1) }}%)</span>
                    @endif
                </span>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
