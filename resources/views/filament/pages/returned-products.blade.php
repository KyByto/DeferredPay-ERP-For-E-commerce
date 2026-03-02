<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Produits en stock</h2>
            <p class="text-3xl font-bold text-primary-600 mt-2">{{ $totalCount }}</p>
        </div>
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Valeur stock</h2>
            <p class="text-3xl font-bold text-gray-700 dark:text-gray-300 mt-2">{{ number_format($totalValue, 2) }} DZD</p>
        </div>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Produit</th>
                    <th scope="col" class="px-6 py-3">Quantite</th>
                    <th scope="col" class="px-6 py-3">De commandes</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returnedItems as $index => $item)
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
                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                            {{ $item['quantity'] }} unite(s)
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs text-gray-600 dark:text-gray-300">
                                {{ implode(', ', $item['orders']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <x-filament::button size="sm" color="success" wire:click="openSellModal({{ $index }})">
                                Vendre
                            </x-filament::button>
                            <x-filament::button size="sm" color="danger" wire:click="openDeleteModal({{ $index }})" class="ml-2">
                                Supprimer
                            </x-filament::button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            Aucun produit en stock retour pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-filament::modal id="sell-modal" wire:model="sellModalOpen">
        <x-slot name="heading">Vendre Produit Retourne</x-slot>

        <div class="space-y-4">
            <div class="text-sm text-gray-600 dark:text-gray-300">
                Produit: <span class="font-semibold">{{ $selectedProductName }}</span>
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300">Quantite ({{ $selectedAvailable }} disponible)</label>
                <input type="number" min="1" wire:model.live="sellQuantity" class="w-full mt-1 rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300">Client</label>
                <input type="text" wire:model.live="sellClient" class="w-full mt-1 rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300">Tel</label>
                <input type="text" wire:model.live="sellPhone" class="w-full mt-1 rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300">Prix (DZD)</label>
                <input type="number" min="0" step="0.01" wire:model.live="sellPrice" class="w-full mt-1 rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300">Canal</label>
                <select wire:model.live="sellCanal" class="w-full mt-1 rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                    <option value="whatsapp">WhatsApp</option>
                    <option value="facebook">Facebook</option>
                    <option value="instagram">Instagram</option>
                    <option value="telephone">Telephone</option>
                </select>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex gap-2">
                <x-filament::button color="gray" wire:click="$set('sellModalOpen', false)">Annuler</x-filament::button>
                <x-filament::button color="success" wire:click="sellProduct">Creer commande</x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    <x-filament::modal id="delete-modal" wire:model="deleteModalOpen">
        <x-slot name="heading">Supprimer Produits</x-slot>

        <div class="space-y-4">
            <div class="text-sm text-gray-600 dark:text-gray-300">
                Produit: <span class="font-semibold">{{ $selectedProductName }}</span>
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300">Quantite ({{ $selectedAvailable }} disponible)</label>
                <input type="number" min="1" wire:model.live="deleteQuantity" class="w-full mt-1 rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300">Raison</label>
                <textarea wire:model.live="deleteReason" rows="3" class="w-full mt-1 rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700"></textarea>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex gap-2">
                <x-filament::button color="gray" wire:click="$set('deleteModalOpen', false)">Annuler</x-filament::button>
                <x-filament::button color="danger" wire:click="deleteProduct">Supprimer</x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
