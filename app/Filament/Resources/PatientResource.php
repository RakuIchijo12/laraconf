<?php

namespace App\Filament\Resources;

use App\Enums\ClassificationEnum;
use App\Filament\Resources\PatientResource\Pages;
use App\Models\Patient;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationParentItem = 'Patient Registration';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->placeholder('Enter patient name')
                    ->required()
                    ->maxLength(255)
                    ->regex('/^[A-Za-z\s]+$/')
                    ->minLength(3)
                    ->columnSpanFull(),

                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\DatePicker::make('birthdate')
                            ->native(false)
                            ->displayFormat('m/d/Y')
                            ->required()
                            ->live()
                            ->maxDate(today())
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('age_display', Patient::computeAgeDisplay($state));
                                }
                            }),

                        Forms\Components\Placeholder::make('age_display')
                            ->label('Age')
                            ->content(function (callable $get, $record) {
                                if ($get('age_display')) {
                                    return $get('age_display');
                                }
                                if ($record?->birthdate) {
                                    return $record->age_display;
                                }
                                return '—';
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Select::make('sex')
                    ->options([
                        'male'   => 'Male',
                        'female' => 'Female',
                    ])
                    ->required(),

                Forms\Components\Select::make('classification')
                    ->searchable()
                    ->multiple()
                    ->options(ClassificationEnum::toArray())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('ref_id')
                    ->label('Reference ID')
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('birthdate')
                    ->searchable()
                    ->date(),
                Tables\Columns\TextColumn::make('age_display')
                    ->label('Age')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sex')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('classification')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $classifications = $record->classification ?? [];
                        if (is_string($classifications)) {
                            $classifications = json_decode($classifications, true) ?? [];
                        }
                        return collect($classifications)
                            ->map(fn ($s) => ucwords(str_replace('-', ' ', $s)))
                            ->join('<br>');
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered At')
                    ->dateTime('F j, Y g:i A'),
            ])
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
            'index'  => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'edit'   => Pages\EditPatient::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}