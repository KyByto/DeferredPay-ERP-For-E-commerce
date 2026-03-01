<x-filament::widget>
    <x-filament::card>
        <div class="text-sm text-gray-500">Stock Retours</div>
        <div class="mt-3 text-2xl font-semibold">{{ $totalCount }} produits</div>
        <div class="mt-2 text-sm">Valeur: {{ number_format($totalValue, 2) }} DZD</div>
        <div class="mt-4">
            <a href="{{ $manageUrl }}" class="text-primary-600 text-sm">Gerer stock</a>
        </div>
    </x-filament::card>
</x-filament::widget>
