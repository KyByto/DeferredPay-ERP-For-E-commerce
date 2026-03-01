<x-filament-panels::page>
    <form wire:submit.prevent="create" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-3">
            <x-filament::button type="button" color="gray" wire:click="cancel">
                Annuler
            </x-filament::button>
            <x-filament::button type="submit">
                Creer la commande
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
