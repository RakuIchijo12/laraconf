<?php

namespace App\Filament\Resources;

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
                                    $birthdate = Carbon::parse($state);
                                    $now = Carbon::now();

                                    if ($birthdate->isFuture()) {
                                        $set('age_display', '0');
                                        return;
                                    }

                                    $years = (int) $birthdate->diffInYears($now);
                                    if ($years >= 1) {
                                        $set('age_display', $years . ' year' . ($years > 1 ? 's' : ''));
                                        return;
                                    }

                                    $months = (int) $birthdate->diffInMonths($now);
                                    if ($months >= 1) {
                                        $set('age_display', $months . ' month' . ($months > 1 ? 's' : ''));
                                        return;
                                    }

                                    $days = (int) $birthdate->diffInDays($now);
                                    $set('age_display', $days === 0 ? '0' : $days . ' day' . ($days > 1 ? 's' : ''));
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
                    ->options([
                        'senior-citizen'        => 'Senior Citizen',
                        'person-with-disability' => 'Person with Disability',
                        'employee'              => 'Employee',
                        'dependent'             => 'Dependent',
                    ])
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
                    ->searchable()
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('classification')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('-', ' ', $state))),
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