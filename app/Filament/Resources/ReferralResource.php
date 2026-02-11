<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralResource\Pages;
use App\Filament\Resources\ReferralResource\RelationManagers;
use App\Models\Referral;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Referral Information')
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->relationship('patient', 'first_name')
                            ->searchable()
                            ->required(),

                        // Forms\Components\Select::make('from_doctor_id')
                        //     ->relationship('fromDoctor', 'name')
                        //     ->required(),

                        // Forms\Components\Select::make('to_department_id')
                        //     ->relationship('toDepartment', 'name')
                        //     ->live()
                        //     ->required(),

                        // Forms\Components\Select::make('to_doctor_id')
                        //     ->label('Assign to Specific Doctor')
                        //     ->options(function (Get $get) {
                        //         return Doctor::where('department_id', $get('to_department_id'))
                        //             ->pluck('name', 'id');
                        //     })
                        //     ->searchable()
                        //     ->visible(fn (Get $get) => filled($get('to_department_id'))),

                        // Forms\Components\Textarea::make('reason')
                        //     ->required(),

                        // Forms\Components\Textarea::make('notes'),

                        // Forms\Components\Select::make('status')
                        //     ->options([
                        //         'pending' => 'Pending',
                        //         'accepted' => 'Accepted',
                        //         'completed' => 'Completed',
                        //         'rejected' => 'Rejected',
                        //     ])
                        //     ->default('pending')
                        //     ->required(),

                    ])
                    ->columns(2),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Patient')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListReferrals::route('/'),
            'create' => Pages\CreateReferral::route('/create'),
            'edit' => Pages\EditReferral::route('/{record}/edit'),
        ];
    }
}
