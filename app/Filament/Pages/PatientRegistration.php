<?php

namespace App\Filament\Pages;

use App\Enums\ClassificationEnum;
use Filament\Forms;
use Filament\Pages\Page;
use App\Models\Patient;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class PatientRegistration extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static string $view = 'filament.pages.patient-registration';
    protected static ?string $navigationLabel = 'Patient Registration';

    public ?array $data = [];

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label('Name')
                ->placeholder('Enter patient name')
                ->required()
                ->maxLength(255)
                ->regex('/^[A-Za-z\s]+$/') 
                ->minLength(3),

           Forms\Components\Grid::make('Birth Information')
                ->schema([
                    Forms\Components\DatePicker::make('birthdate')
                        ->native(false)
                        ->displayFormat('m/d/Y')
                        ->required()
                        ->live()
                        ->default(today()->subYears(20))
                        ->maxDate(today())
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $set('age_display', Patient::computeAgeDisplay($state));
                            }
                        }),

                    Forms\Components\Placeholder::make('age_display')
                        ->label('Age')
                        ->content(fn (callable $get) => $get('age_display') ?? '—'),
                ])
                ->columns(2),

            Forms\Components\Select::make('sex')
                ->options([
                    'male' => 'Male',
                    'female' => 'Female',
                ])
                ->required(),
            
            Forms\Components\Select::make('classification')
                ->multiple()
                ->options(ClassificationEnum::toArray())
                ->required(),
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        # Validate senior citizen classification against age
        $age = (int) Carbon::parse($data['birthdate'])->diffInYears(now());

        if (
            in_array('senior-citizen', $data['classification'] ?? []) &&
            $age < 60
        ) {
            Notification::make()
                ->title('Registration failed')
                ->body('Senior citizens must be 60 years old or above.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'data.birthdate' => 'Senior citizens must be 60 years old or above.',
            ]);
        }

        # Check for duplicate patient based on name and birthdate
        $exists = Patient::where('name', $data['name'])
            ->where('birthdate', $data['birthdate'])
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Duplicate Record')
                ->body('A patient with the same name and birthdate already exists.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'data.name' => 'Patient already exists.',
            ]);
        }

        # Create the patient record
        Patient::create($data);

        $this->form->fill();

        Notification::make()
            ->title('Patient registered successfully!')
            ->body('The patient has been added to the system.')
            ->success()
            ->send();
            
        $this->redirect(\App\Filament\Pages\TransactionEntry::getUrl());
    }
}