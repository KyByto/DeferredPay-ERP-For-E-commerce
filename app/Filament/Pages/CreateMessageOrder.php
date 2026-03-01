<?php

namespace App\Filament\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class CreateMessageOrder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Nouvelle (Messages)';

    protected static ?string $navigationGroup = 'Commandes';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Nouvelle Commande Messages';

    protected static string $view = 'filament.pages.create-message-order';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'canal_messages' => 'whatsapp',
            'items' => [],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Canal')
                    ->schema([
                        Forms\Components\Select::make('canal_messages')
                            ->label('Canal')
                            ->options([
                                'whatsapp' => 'WhatsApp',
                                'facebook' => 'Facebook',
                                'instagram' => 'Instagram',
                                'telephone' => 'Telephone',
                            ])
                            ->required(),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Client')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Client')
                            ->required(),
                        Forms\Components\TextInput::make('customer_phone')
                            ->label('Tel')
                            ->required(),
                        Forms\Components\TextInput::make('customer_address')
                            ->label('Adresse')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Produits')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label(false)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Produit')
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Qte')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                                Forms\Components\TextInput::make('price')
                                    ->label('Prix')
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix('DZD')
                                    ->required(),
                            ])
                            ->columns(3)
                            ->createItemButtonLabel('Ajouter produit +')
                            ->minItems(1)
                            ->required(),
                        Forms\Components\Placeholder::make('total')
                            ->label('Total')
                            ->content(function (Forms\Get $get): string {
                                $items = $get('items') ?? [];
                                $total = collect($items)->sum(function ($item) {
                                    $qty = (float) ($item['quantity'] ?? 0);
                                    $price = (float) ($item['price'] ?? 0);

                                    return $qty * $price;
                                });

                                return number_format($total, 2).' DZD';
                            }),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes'),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $items = collect($data['items'] ?? [])->map(function ($item) {
            return [
                'name' => $item['name'] ?? 'Produit',
                'quantity' => (int) ($item['quantity'] ?? 0),
                'price' => (float) ($item['price'] ?? 0),
            ];
        })->filter(fn ($item) => $item['quantity'] > 0)->values()->all();

        $subtotal = collect($items)->sum(fn ($item) => $item['quantity'] * $item['price']);

        Order::create([
            'shopify_id' => 'msg-'.Str::uuid(),
            'name' => $this->generateMessageOrderName(),
            'email' => null,
            'total_price' => $subtotal,
            'subtotal_price' => $subtotal,
            'shipping_price' => 0,
            'status' => 'confirmed',
            'items' => $items,
            'order_date' => now(),
            'source' => 'messages',
            'canal_messages' => $data['canal_messages'] ?? 'whatsapp',
            'customer_name' => $data['customer_name'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_address' => $data['customer_address'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        Notification::make()
            ->title('Commande messages creee')
            ->success()
            ->send();

        $this->redirect(OrderResource::getUrl('index'));
    }

    public function cancel(): void
    {
        $this->redirect(OrderResource::getUrl('index'));
    }

    private function generateMessageOrderName(): string
    {
        $lastName = Order::where('source', 'messages')
            ->where('name', 'like', 'MSG-%')
            ->orderByDesc('id')
            ->value('name');

        $next = 1;

        if ($lastName && preg_match('/MSG-(\d+)/', $lastName, $matches)) {
            $next = (int) $matches[1] + 1;
        }

        return 'MSG-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
