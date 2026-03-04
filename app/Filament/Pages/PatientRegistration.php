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
                        ->displayFormat('m/d/Y')
                        ->required()
                        ->live()
                        ->default('2001-06-22')
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
                ->multiple()
                ->options([
                    'senior-citizen'         => 'Senior Citizen (20%)',
                    'person-with-disability' => 'Person with Disability (20%)',
                    'employee'               => 'Employee (100%)',
                    'dependent'              => 'Dependent (25%)',
                ])
                ->required(),
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();

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

        Patient::create($data);

        $this->form->fill();

        Notification::make()
            ->title('Patient registered successfully!')
            ->body('The patient has been added to the system.')
            ->success()
            ->send();
    }
}