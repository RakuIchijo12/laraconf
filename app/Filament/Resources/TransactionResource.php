<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Transaction History';
    protected static ?string $navigationParentItem = 'Transaction Entry';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->recordUrl(false)
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('ID')
                    ->searchable()
                    ->color('primary')
                    ->badge(),

                Tables\Columns\TextColumn::make('patient.name')
                    ->label('Patient')
                    ->searchable(),

                Tables\Columns\TextColumn::make('patient.classification')
                    ->label('Classification')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $classifications = $record->patient->classification ?? [];
                        if (is_string($classifications)) {
                            $classifications = json_decode($classifications, true) ?? [];
                        }
                        return collect($classifications)
                            ->map(fn ($s) => ucwords(str_replace('-', ' ', $s)))
                            ->join('<br>');
                    }),

                Tables\Columns\TextColumn::make('discount_rate')
                    ->label('Discount Rate')
                    ->formatStateUsing(fn ($state) => (int) $state . '%'),

                Tables\Columns\TextColumn::make('gross_amount')
                    ->label('Gross Amount')
                    ->formatStateUsing(fn ($state) => '₱ ' . number_format($state, 2)),

                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Discount Amount')
                    ->formatStateUsing(fn ($state) => '₱ ' . number_format($state, 2)),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->formatStateUsing(fn ($state) => '₱ ' . number_format($state, 2)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('F j, Y g:i A'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}