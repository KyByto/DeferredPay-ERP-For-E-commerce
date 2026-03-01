<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\FinancialTransaction;
use App\Services\TreasuryEngine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpenseResource extends Resource
{
    protected static ?string $model = FinancialTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Sorties d\'Argent';

    protected static ?string $modelLabel = 'Sortie d\'Argent';

    protected static ?string $navigationGroup = 'Tresorerie';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', TreasuryEngine::TYPE_EXPENSE);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('type')
                    ->default(TreasuryEngine::TYPE_EXPENSE),
                Forms\Components\DatePicker::make('created_at')
                    ->label('Date')
                    ->default(now())
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->label('Montant'),
                Forms\Components\Select::make('categorie')
                    ->label('Categorie')
                    ->options(\App\Models\FinancialTransaction::CATEGORY_OPTIONS)
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpanFull(),
            ]);
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
                    ->color(fn (string $state): string => match ($state) {
                        TreasuryEngine::TYPE_EXPENSE => 'danger',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->money('DZD')
                    ->label('Montant'),
                Tables\Columns\TextColumn::make('categorie')
                    ->label('Categorie')
                    ->formatStateUsing(fn (?string $state) => \App\Models\FinancialTransaction::CATEGORY_OPTIONS[$state] ?? $state)
                    ->limit(30),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                // Immutable transactions
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
        ];
    }
}
