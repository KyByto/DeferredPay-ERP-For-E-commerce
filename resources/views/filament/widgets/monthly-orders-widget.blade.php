<x-filament::widget>
    <x-filament::card>
        <div class="text-sm text-gray-500">Ce Mois</div>
        <div class="mt-3 space-y-1 text-sm">
            <div>Shopify: {{ $shopifyCount }}</div>
            <div>Messages: {{ $messagesCount }}</div>
            <div class="font-semibold">Total: {{ $totalCount }}</div>
        </div>
        <div class="mt-4 text-sm">
            <div>CA: {{ number_format($grossRevenue, 2) }} DZD</div>
            <div>Retours: {{ $returnedCount }} ({{ number_format($returnsRate, 1) }}%)</div>
        </div>
    </x-filament::card>
</x-filament::widget>
