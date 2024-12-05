<?php

namespace App\Models;

use App\Enums\Region;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Tabs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conference extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'region' => Region::class,
        'venue_id' => 'integer',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class);
    }

    public function talks(): BelongsToMany
    {
        return $this->belongsToMany(Talk::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }


    public static function getForm(): array
{
    return [
        Forms\Components\Tabs::make()
            ->columnSpanFull()
            ->tabs([
                Tabs\Tab::make('Conference Details')
                    ->collapsible()
                    ->description('Provide some basic information about the conference.')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->columnSpanFull()
                            ->label('Conference')
                            ->default('My Conference')
                            ->maxLength(60)
                            ->required(),
                        Forms\Components\MarkdownEditor::make('description')
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->required(),
                        Forms\Components\Fieldset::make('Status')
                            ->columns(1)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Published',
                                        'archived' => 'Archived',
                                    ]),
                                Forms\Components\Toggle::make('is_published')
                                    ->label('Published')
                                    ->default(true),
                            ]),
                    ]),
                Tabs\Tab::make('Location')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('region')
                            ->live()
                            ->enum(Region::class)
                            ->options(Region::class)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('venue_id', null);
                            }),
                        Forms\Components\Select::make('venue_id')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(Venue::getForm())
                            ->editOptionForm(Venue::getForm())
                            ->relationship('venue', 'name', modifyQueryUsing: function (Builder $query, Forms\Get $get) {
                                return $query->where('region', $get('region'));
                            }),
                    ]),
            ]),
        Actions::make([
            Action::make('star')
                ->label('Fill with Factory Data')
                ->icon('heroicon-m-star')
                ->visible(function(string $operation){
                    if($operation != 'create') {
                        return false;
                    }
                    if(! app()->environment('local')) {
                        return false;   
                    }
                    return true;
                })
                ->action(function ($livewire) {
                    $data = Conference::factory()->make()->toArray();
                    $livewire->form->fill($data);
                }),
        ]),
    ];
  }
}
