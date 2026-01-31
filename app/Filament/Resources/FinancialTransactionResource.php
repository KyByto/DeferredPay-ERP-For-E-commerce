<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinancialTransactionResource\Pages;
use App\Models\FinancialTransaction;
use App\Services\TreasuryEngine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FinancialTransactionResource extends Resource
{
    protected static ?string $model = FinancialTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Journal Financier';
    protected static ?string $modelLabel = 'Transaction';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }
    
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Date')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('DZD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('source_type')
                    ->label('Source')
                    ->formatStateUsing(fn ($state, FinancialTransaction $record) => $record->source ? class_basename($record->source) . " #{$record->source->id}" : ($record->source_type ?? '-')),
                Tables\Columns\TextColumn::make('destination_type')
                    ->label('Destination')
                    ->formatStateUsing(fn ($state, FinancialTransaction $record) => $record->destination ? class_basename($record->destination) . " #{$record->destination->id}" : ($record->destination_type ?? '-')),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        TreasuryEngine::TYPE_DEBT => 'Dette',
                        TreasuryEngine::TYPE_PAYMENT => 'Paiement',
                        TreasuryEngine::TYPE_EXPENSE => 'Frais',
                        TreasuryEngine::TYPE_INCOME => 'Entrée',
                        TreasuryEngine::TYPE_TRANSFER => 'Transfert',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinancialTransactions::route('/'),
        ];
    }
}