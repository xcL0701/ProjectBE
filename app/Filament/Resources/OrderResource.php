<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\OrderLink;
use App\Models\ProductPhoto;
use App\Models\Product;
use App\Models\Cart;
use App\Models\OrderItems;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Carbon\Carbon;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentsRelationManager;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Str;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationLabel = 'Pesanan';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('id')
                ->label('Order ID')
                ->default('CSIORD-' . strtoupper(Str::random(8)))
                ->required()
                ->disabled(fn ($livewire) => $livewire instanceof Pages\EditOrder),

            Forms\Components\Select::make('cart_id')
                ->label('Pilih Keranjang (Cart)')
                ->options(
                    \App\Models\Cart::with('user')
                        ->get()
                        ->filter(fn ($cart) => $cart->user)
                        ->mapWithKeys(fn ($cart) => [
                            $cart->id => 'Cart #' . $cart->id . ' - ' . $cart->user->name
                        ])
                        ->toArray()
                )
                ->searchable()
                ->preload()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $cart = \App\Models\Cart::with('cartItems.product')->find($state);
                    if ($cart) {
                        $set('user_id', $cart->user_id);
                        $set('shipping_method', $cart->shipping_method);

                        $items = $cart->cartItems->map(function ($item) {
                            return [
                                'product_id' => $item->product_id,
                                'product_name' => $item->product?->name,
                                'quantity' => $item->quantity,
                            ];
                        })->toArray();

                        $set('cart_items', $items);

                        $set('order_items', collect($items)->map(fn ($item) => [
                            'product_id' => $item['product_id'],
                            'product_name' => $item['product_name'],
                            'unit_price' => null,
                            'quantity' => $item['quantity'],
                        ])->toArray());
                    } else {
                        $set('cart_items', []);
                        $set('order_items', []);
                    }
                }),

            Forms\Components\Select::make('user_id')
                ->label('User')
                ->disabled()
                ->dehydrated()
                ->options(\App\Models\User::all()->pluck('name', 'id')),

            Forms\Components\Select::make('shipping_method')
                ->label('Metode Pengiriman')
                ->options([
                    'pickup' => 'Ambil di Tempat',
                    'delivery' => 'Diantar',
                ])
                ->disabled()
                ->dehydrated(true),

            Forms\Components\Hidden::make('cart_items')
                ->reactive()
                ->afterStateHydrated(function (callable $set, $state) {
                    if (!$state) {
                        $set('cart_items', []);
                    }
                }),

            Forms\Components\Textarea::make('address')
                ->label('Alamat')
                ->rows(3),

            Forms\Components\Textarea::make('note')
                ->label('Catatan')
                ->rows(3),

            Forms\Components\TextInput::make('shipping_cost')
                ->label('Ongkir')
                ->prefix('Rp')
                ->numeric(),

            Forms\Components\TextInput::make('initial_payment')
                ->label('Total yang Harus Dibayar (DP)')
                ->numeric()
                ->prefix('Rp')
                ->helperText('Kosongkan jika harus bayar full')
                ->columnSpan(1),

            Forms\Components\Repeater::make('order_items')
                ->relationship('orderItems')
                ->dehydrated(true)
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->label('Produk')
                        ->options(\App\Models\Product::all()->pluck('name', 'id'))
                        ->disabled()
                        ->dehydrated(true)
                        ->default(fn (callable $get) => $get('product_id')),

                    Forms\Components\TextInput::make('unit_price')
                        ->label('Harga per Barang')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->dehydrated(true)
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            $set('total_price', ($get('unit_price') ?? 0) * ($get('quantity') ?? 1));
                        }),

                    Forms\Components\TextInput::make('quantity')
                        ->label('Jumlah')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(true)
                        ->reactive()
                        ->default(function (callable $get) {
                            $items = $get('../../cart_items') ?? [];
                            $productId = $get('product_id');
                            return collect($items)->firstWhere('product_id', $productId)['quantity'] ?? null;
                        })
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            $set('total_price', ($get('unit_price') ?? 0) * ($get('quantity') ?? 1));
                        }),

                    Forms\Components\TextInput::make('total_price')
                        ->label('Total Harga')
                        ->numeric()
                        ->prefix('Rp')
                        ->disabled()
                        ->dehydrated(true)
                        ->required()
                        ->default(fn (callable $get) => ($get('unit_price') ?? 0) * ($get('quantity') ?? 1))
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            $set('total_price', ($get('unit_price') ?? 0) * ($get('quantity') ?? 1));
                        }),
                ])
                ->columns(2),


            Forms\Components\TextInput::make('total_price')
                ->label('Total Harga (Keseluruhan)')
                ->prefix('Rp')
                ->disabled()
                ->dehydrated(true)
                ->reactive()
                ->default(function (callable $get) {
                    $items = $get('order_items') ?? [];
                    $shipping = $get('shipping_cost') ?? 0;
                    $total = collect($items)->sum(fn ($item) => ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1));
                    return $total + $shipping;
                })
                ->afterStateUpdated(function (callable $set, callable $get) {
                    $items = $get('order_items') ?? [];
                    $shipping = $get('shipping_cost') ?? 0;
                    $total = collect($items)->sum(fn ($item) => ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1));
                    $set('total_price', $total + $shipping);
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('shipping_method'),
                Tables\Columns\TextColumn::make('total_price')->money('IDR'),
                Tables\Columns\TextColumn::make('calculated_total_paid')->money('IDR'),
                Tables\Columns\TextColumn::make('remaining_amount')->sortable()->money('IDR')->label('Sisa'),
                Tables\Columns\BadgeColumn::make('status')->sortable()->searchable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Belum Lunas',
                        'paid' => 'Lunas',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                    ]),
                TextColumn::make('link.token')
                    ->label('Link Konfirmasi')
                    ->formatStateUsing(fn ($state) => "ptcsi.vercel.app/payment/confirmation/{$state}")
                    ->copyable()
                    ->copyMessage('Link berhasil disalin')
                    ->copyMessageDuration(1500)
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Belum Dibayar',
                        'partial' => 'Cicilan',
                        'paid' => 'Lunas',
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class,
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrder::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('link');
    }
}
