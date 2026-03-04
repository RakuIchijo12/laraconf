<?php

namespace App\Filament\Pages;

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
                        ->required()
                        ->live()
                        ->maxDate(today())
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $birthdate = Carbon::parse($state);
                                $now = Carbon::now();

                                if ($birthdate->isFuture()) {
                                    $set('age_display', '0 days');
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
                ->options([
                    'senior-citizen' => 'Senior Citizen',
                    'person-with-disability' => 'Person with Disability',
                    'employee' => 'Employee',
                    'dependent' => 'Dependent',
                ])
                ->required(),
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        if (
            ($data['classification'] ?? null) === 'senior-citizen' &&
            $data['age'] < 60
        ) {
            Notification::make()
                ->title('Registration failed')
                ->body('Senior citizens must be 60 years old or above to register under Senior Citizen classification.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'age' => 'Senior citizens must be 60 years old or above.',
            ]);
        }

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
                'name' => 'Patient already exists.',
            ]);
        }

        Patient::create($data);

        $this->form->fill();

        Notification::make()
            ->title('Patient registered successfully!')
            ->success()
            ->send();
    }
}