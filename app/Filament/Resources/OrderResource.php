<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Commandes Shopify';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Détails de la Commande')
                ->schema([
                    Forms\Components\TextInput::make('name')->label('N° Commande')->readonly(),
                    Forms\Components\TextInput::make('email')->label('Email Client')->readonly(),
                    Forms\Components\TextInput::make('subtotal_price')->label('Total Produits')->prefix('DZD')->readonly(),
                    Forms\Components\TextInput::make('status')->label('Statut')->readonly(),
                ])->columns(2),

            Forms\Components\Section::make('Produits Commandés')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->label(false)
                        ->schema([
                            Forms\Components\Grid::make(4)
                                ->schema([
                                    Forms\Components\ViewField::make('image_url')
                                        ->label('Photo')
                                        ->view('filament.forms.components.image-display')
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('name')
                                        ->label('Produit')
                                        ->readonly()
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Qté')
                                        ->readonly(),
                                    Forms\Components\TextInput::make('price')
                                        ->label('Prix Unit.')
                                        ->suffix('DZD')
                                        ->readonly(),
                                ])
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->disableItemMovement()
                        ->columns(1),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('N° Commande')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->icon('heroicon-m-envelope')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subtotal_price')
                    ->label('Prix Produits')
                    ->money('DZD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid', 'fulfilled', 'delivered' => 'success',
                        'pending', 'shipping' => 'warning',
                        'returned', 'refunded' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\ImageColumn::make('items')
                    ->label('Aperçu')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->state(function (Order $record) {
                        return collect($record->items)
                            ->pluck('image_url')
                            ->filter()
                            ->toArray();
                    }),
                Tables\Columns\TextColumn::make('order_date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('order_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Filtrer par Statut')
                    ->options([
                        'pending' => 'En attente',
                        'paid' => 'Payée',
                        'fulfilled' => 'Traitée (Shopify)',
                        'shipping' => 'En Livraison',
                        'delivered' => 'Livrée',
                        'returned' => 'Retour',
                        'refunded' => 'Remboursée',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                // Action 1: En Livraison
                Tables\Actions\Action::make('mark_shipping')
                    ->label('En Livraison')
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => in_array($record->status, ['pending', 'fulfilled']))
                    ->action(fn (Order $record) => $record->update(['status' => 'shipping'])),

                // Action 2: Livré (Final)
                Tables\Actions\Action::make('mark_delivered')
                    ->label('Livré')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'shipping')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'delivered']);

                        // Add money to Societe
                        $treasury = new \App\Services\TreasuryEngine();
                        $treasury->addDeliveryCollection(
                            amount: $record->total_price, // Use Total (Product + Shipping)
                            description: "Commande {$record->name} livrée"
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Livré & Encaissé')
                            ->success()
                            ->send();
                    }),

                // Action 3: Retour (Echec)
                Tables\Actions\Action::make('mark_returned')
                    ->label('Retour')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'shipping')
                    ->action(fn (Order $record) => $record->update(['status' => 'returned'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('generate_restock_pdf')
                        ->label('Générer PDF Stock')
                        ->icon('heroicon-o-printer')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            return (new \App\Filament\Actions\Order\GenerateRestockPdf())($records);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
        ];
    }
}