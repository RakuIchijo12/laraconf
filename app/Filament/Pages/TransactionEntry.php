<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Item;
use App\Models\Patient;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\RawJs;
use Illuminate\Validation\ValidationException;

class TransactionEntry extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static string $view = 'filament.pages.transaction-entry';
    protected static ?string $navigationLabel = 'Transaction Entry';

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
            Forms\Components\Select::make('patient_id')
                ->label('Patient')
                ->allowHtml()
                ->options(
                    Patient::all()->map(function ($patient) {
                        return [
                            'value' => $patient->id,
                            'label' => '<strong>' . e($patient->name) . '</strong><br><small style="color: #6b7280;">' . e($patient->ref_id) . '</small>',
                        ];
                    })->pluck('label', 'value')
                )
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(function (callable $set) {
                    $this->recalculate($set);
                }),

            Forms\Components\Section::make('Patient Discount Info')
                ->schema([
                    Forms\Components\Placeholder::make('patient_name')
                        ->label('Patient Name')
                        ->content(function (callable $get) {
                            $patient = Patient::find($get('patient_id'));
                            if (!$patient) return '—';
                            return $patient->name;
                        }),

                    Forms\Components\Placeholder::make('patient_ref_id')
                        ->label('Reference ID')
                        ->content(function (callable $get) {
                            $patient = Patient::find($get('patient_id'));
                            if (!$patient) return '—';
                            return $patient->ref_id;
                        }),

                    Forms\Components\Placeholder::make('patient_classification')
                        ->label('Classification')
                        ->content(function (callable $get) {
                            $patient = Patient::find($get('patient_id'));
                            if (!$patient) return '—';
                            $classifications = $patient->classification ?? [];
                            return new \Illuminate\Support\HtmlString(
                                collect($classifications)
                                    ->map(fn ($s) => '• ' . ucwords(str_replace('-', ' ', $s)))
                                    ->join('<br>')
                            );
                        }),

                    Forms\Components\Placeholder::make('patient_discount')
                        ->label('Applicable Discount')
                        ->content(function (callable $get) {
                            $patient = Patient::find($get('patient_id'));
                            if (!$patient) return '—';
                            return (int) $patient->discount_rate . '%'; 
                        }),
                ])
                ->columns(4)
                ->visible(fn (callable $get) => (bool) $get('patient_id')),

            Forms\Components\Section::make('Items')
                ->schema([
                    Forms\Components\Repeater::make('transaction_items')
                        ->label('')
                        ->schema([
                            Forms\Components\Select::make('item_id')
                                ->label('Item')
                                ->placeholder('Search or create an item...')
                                ->options(Item::all()->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->live()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('price')
                                        ->required()
                                        ->numeric()
                                        ->prefix('₱')
                                        ->step(0.01)
                                        ->inputMode('decimal')
                                        ->minValue(0),
                                ])
                                ->createOptionUsing(function (array $data) {
                                    return Item::create($data)->id;
                                })
                                ->editOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('price')
                                        ->required()
                                        ->numeric()
                                        ->prefix('₱')
                                        ->minValue(0),
                                ])
                                ->fillEditOptionActionFormUsing(function ($state): array {
                                    $item = Item::find($state);
                                    return [
                                        'name'  => $item?->name,
                                        'price' => $item?->price,
                                    ];
                                })
                                ->editOptionAction(function (Forms\Components\Actions\Action $action) {
                                    return $action->action(function (array $data, $state) {
                                        $item = Item::find($state);
                                        if ($item) {
                                            $item->update($data);
                                        }
                                    });
                                })
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $item = Item::find($state);
                                    if ($item) {
                                        $set('unit_price', (float) $item->price);
                                        $quantity = (int) ($get('quantity') ?? 1);
                                        $set('subtotal', (float) $item->price * $quantity);
                                    }
                                    $this->recalculate($set);
                                }),

                            Forms\Components\TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required()
                                ->lazy()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $price = (float) ($get('unit_price') ?? 0);
                                    $set('subtotal', $price * (int) ($state ?? 1));
                                    $this->recalculate($set);
                                }),

                            Forms\Components\Placeholder::make('unit_price')
                                ->label('Unit Price')
                                ->content(fn (callable $get) => $get('unit_price') ? '₱ ' . number_format((float) $get('unit_price'), 2) : '—'),

                            Forms\Components\Placeholder::make('subtotal')
                                ->label('Subtotal')
                                ->content(fn (callable $get) => $get('subtotal') ? '₱ ' . number_format((float) $get('subtotal'), 2) : '—'),
                        ])
                        ->columns(4)
                        ->live()
                        ->afterStateUpdated(function (callable $set) {
                            $this->recalculate($set);
                        })
                        ->minItems(1)
                        ->addActionLabel('Add Item')
                        ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action
                                ->requiresConfirmation()
                                ->modalHeading('Remove Item')
                                ->modalDescription('Are you sure you want to remove this item from the transaction?')
                                ->modalSubmitActionLabel('Yes, remove it')
                        ),
                ]),

            Forms\Components\Section::make('Summary')
                ->schema([
                    Forms\Components\Placeholder::make('gross_amount_display')
                        ->label('Gross Amount')
                        ->content(fn (callable $get) => '₱ ' . number_format((float) ($get('gross_amount') ?? 0), 2)),

                    Forms\Components\Placeholder::make('discount_amount_display')
                        ->label('Discount Amount')
                        ->content(fn (callable $get) => '₱ ' . number_format((float) ($get('discount_amount') ?? 0), 2)),

                    Forms\Components\Placeholder::make('total_amount_display')
                        ->label('Total Amount')
                        ->content(fn (callable $get) => '₱ ' . number_format((float) ($get('total_amount') ?? 0), 2)),
                ])
                ->columns(3),
        ];
    }

    protected function recalculate(callable $set): void
    {
        $items = $this->data['transaction_items'] ?? [];
        $patientId = $this->data['patient_id'] ?? null;

        #sum up gross amount
        $gross = collect($items)->sum(function ($row) {
            $item = Item::find($row['item_id'] ?? null);
            return (float) ($item?->price ?? 0) * (int) ($row['quantity'] ?? 1);
        });

        #discount rate based on patient classification
        $discountRate = 0;
        if ($patientId) {
            $patient = Patient::find($patientId);
            if ($patient) {
                $discountRate = $patient->discount_rate;
            }
        }

        #calculate discount amount and total
        $discountAmount = $gross * ($discountRate / 100);
        $total = $gross - $discountAmount;

        $set('gross_amount', round($gross, 2));
        $set('discount_amount', round($discountAmount, 2));
        $set('total_amount', round($total, 2));
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        # Validate that at least one item has been added
        if (empty($data['transaction_items'])) {
            Notification::make()
                ->title('No items added')
                ->body('Please add at least one item to the transaction.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'transaction_items' => 'At least one item is required.',
            ]);
        }

        $patient = Patient::findOrFail($data['patient_id']);

        # Recalculate amounts to ensure data integrity
        $gross = collect($data['transaction_items'])->sum(function ($row) {
            $item = Item::find($row['item_id']);
            return (float) ($item?->price ?? 0) * (int) ($row['quantity'] ?? 1);
        });

        # Get discount rate from patient classification
        $discountRate = (int) $patient->discount_rate;
        $discountAmount = $gross * ($discountRate / 100);
        $total = $gross - $discountAmount;

        # Create the transaction
        $transaction = Transaction::create([
            'patient_id'      => $patient->id,
            'gross_amount'    => round($gross, 2),
            'discount_rate'   => $discountRate,
            'discount_amount' => round($discountAmount, 2),
            'total_amount'    => round($total, 2),
        ]);

        # Create transaction items
        foreach ($data['transaction_items'] as $row) {
            $item = Item::findOrFail($row['item_id']);
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'item_id'        => $item->id,
                'quantity'       => (int) $row['quantity'],
                'unit_price'     => (float) $item->price,
                'subtotal'       => round((float) $item->price * (int) $row['quantity'], 2),
            ]);
        }

        # Reset the form after submission
        $this->form->fill();

        Notification::make()
            ->title('Transaction saved!')
            ->body('Transaction ID: ' . $transaction->transaction_id)
            ->success()
            ->send();

        $this->redirect(TransactionResource::getUrl('index'));
    }
}