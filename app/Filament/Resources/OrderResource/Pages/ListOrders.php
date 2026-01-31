<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Jobs\SyncShopifyOrders;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync_shopify')
                ->label('Synchroniser Shopify')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    try {
                        SyncShopifyOrders::dispatchSync(); // Run synchronously for UI feedback in this prototype
                        
                        Notification::make()
                            ->title('Synchronisation terminée')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur de synchronisation')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}