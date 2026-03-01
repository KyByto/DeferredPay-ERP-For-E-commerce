<x-filament::widget>
    <x-filament::card>
        <div class="text-sm text-gray-500">Depenses ce Mois</div>
        <div class="mt-3 text-2xl font-semibold">{{ number_format($total, 2) }} DZD</div>
        <div class="mt-4 space-y-1 text-sm">
            <div>Pub: {{ number_format($publicite, 2) }} DZD ({{ number_format($publiciteRate, 1) }}%)</div>
            <div>Personnel: {{ number_format($personnel, 2) }} DZD ({{ number_format($personnelRate, 1) }}%)</div>
            <div>Dollars: {{ number_format($dollars, 2) }} DZD ({{ number_format($dollarsRate, 1) }}%)</div>
        </div>
        <div class="mt-4">
            <a href="{{ \App\Filament\Pages\ExpensesReport::getUrl() }}" class="text-primary-600 text-sm">Voir details</a>
        </div>
    </x-filament::card>
</x-filament::widget>
