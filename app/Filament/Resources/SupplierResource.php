<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Textarea::make('contact_info')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Solde Dû')
                    ->money('DZD')
                    ->getStateUsing(fn (Supplier $record) => $record->balance),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('add_debt')
                    ->label('Ajouter Dette')
                    ->icon('heroicon-o-banknotes')
                    ->color('danger')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->label('Montant'),
                        Forms\Components\Textarea::make('description')
                            ->required(),
                    ])
                    ->action(function (Supplier $record, array $data) {
                        $engine = new \App\Services\TreasuryEngine();
                        $engine->addDebt($record, $data['amount'], $data['description']);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Dette ajoutée')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('pay_supplier')
                    ->label('Payer')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->label('Montant'),
                    ])
                    ->action(function (Supplier $record, array $data) {
                        $engine = new \App\Services\TreasuryEngine();
                        $balance = $engine->getCashBalance();
                        
                        if ($data['amount'] > $balance) {
                            \Filament\Notifications\Notification::make()
                                ->title('Solde Caisse Insuffisant')
                                ->body("Vous avez {$balance} DZD en caisse.")
                                ->danger()
                                ->send();
                            return;
                        }

                        $engine->paySupplier($record, $data['amount']);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Paiement enregistré')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
