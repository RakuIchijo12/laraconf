<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Filament\Resources\PatientResource\RelationManagers;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    // protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationParentItem = 'Patient Registration';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\Section::make('Patient Information')
                //     ->schema([
                //         Forms\Components\TextInput::make('name')
                //             ->required(),

                //         Forms\Components\DatePicker::make('birthdate')
                //             ->native(false)
                //             ->required()
                //             ->maxDate(now()->subDay()),

                //         Forms\Components\TextInput::make('sex')
                //             ->email()
                //             ->required(),
                        
                //     ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->recordUrl(false)
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('ref_id')
                    ->label('Reference ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('birthdate')
                    ->searchable()
                    ->date(),
                Tables\Columns\TextColumn::make('age_display')
                    ->label('Age')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sex')
                    ->searchable(),
                Tables\Columns\TextColumn::make('classification')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return ucwords(str_replace('-', ' ', $state));
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered At')
                    ->dateTime( 'F j, Y g:i A'),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
    }
}
