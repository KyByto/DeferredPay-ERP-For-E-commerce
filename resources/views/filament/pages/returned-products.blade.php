<x-filament-panels::page>
    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Produits en Retour</h2>
            <p class="text-3xl font-bold text-primary-600 mt-2">{{ $totalCount }}</p>
        </div>
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Valeur Vente (Théorique)</h2>
            <p class="text-3xl font-bold text-gray-600 dark:text-gray-300 mt-2">{{ number_format($totalValue, 2) }} DA</p>
        </div>
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Investi (Perte Potentielle)</h2>
            <p class="text-3xl font-bold text-danger-600 mt-2">{{ number_format($totalInvestment, 2) }} DA</p>
        </div>
    </div>

    {{-- Product Table --}}
    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Produit</th>
                    <th scope="col" class="px-6 py-3">Source (Commande)</th>
                    <th scope="col" class="px-6 py-3 text-right">Prix Vente</th>
                    <th scope="col" class="px-6 py-3 text-center">Coût Achat (Unité)</th>
                    <th scope="col" class="px-6 py-3 text-center">Quantité</th>
                    <th scope="col" class="px-6 py-3 text-right">Total Investi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returnedItems as $item)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white flex items-center gap-3">
                            @if($item['image_url'])
                                <img src="{{ $item['image_url'] }}" class="w-10 h-10 rounded object-cover">
                            @else
                                <div class="w-10 h-10 rounded bg-gray-200 flex items-center justify-center text-xs">No Img</div>
                            @endif
                            <div class="flex flex-col">
                                <span class="truncate max-w-xs" title="{{ $item['name'] }}">{{ $item['name'] }}</span>
                                <span class="text-xs text-gray-500">{{ $item['sku'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">
                                {{ $item['order_name'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            {{ number_format($item['unit_price'], 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <input 
                                type="number" 
                                value="{{ $item['cost_price'] }}"
                                wire:change="updateCost('{{ addslashes($item['name']) }}', $event.target.value)"
                                class="w-24 px-2 py-1 text-sm border rounded dark:bg-gray-700 dark:border-gray-600 text-right focus:ring-primary-500 focus:border-primary-500"
                                placeholder="0.00"
                            >
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-gray-900 dark:text-white">
                            {{ $item['quantity'] }}
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-danger-600">
                            {{ number_format($item['total_investment'], 2) }} DA
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Aucun produit en retour pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
