<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Http;


class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Pembayaran';
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('order_id')->disabled(),
            Forms\Components\TextInput::make('amount')->disabled(),
            Forms\Components\FileUpload::make('proof')->image()->disk('public')->disabled(),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_id')->searchable(),
                TextColumn::make('order.user.name')->label('User')->sortable(),
                TextColumn::make('amount')->money('IDR'),
                TextColumn::make('created_at')->sortable()->searchable()->label('Waktu Upload')->dateTime(),
                BadgeColumn::make('status')->sortable()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                ImageColumn::make('proof')
                    ->label('Bukti')
                    ->disk('public')
                    ->height(100)
                    ->width(100),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->status = 'approved';
                        $record->save();
                        // Kirim WA kalau approved
                        Http::withHeaders([
                            'Authorization' => env('WABLAS_API_KEY'),
                            'Content-Type' => 'application/json',
                        ])->post(env('WABLAS_URL'), [
                            'data' => [[
                                'phone' => $record->order->user->phone,
                                'message' => "✅ Pembayaran untuk Order ID: {$record->order_id} sebesar Rp" . number_format($record->amount) . " telah *DITERIMA*. Terima kasih 🙏",
                            ]],
                        ]);
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update(['status' => 'rejected']);

                        // Kirim WA kalau rejected
                        Http::withHeaders([
                            'Authorization' => env('WABLAS_API_KEY'),
                            'Content-Type' => 'application/json',
                        ])->post(env('WABLAS_URL'), [
                            'data' => [[
                                'phone' => $record->order->user->phone,
                                'message' => "❌ Maaf, pembayaran untuk Order ID: {$record->order_id} sebesar Rp" . number_format($record->amount) . " *DITOLAK*. Silakan hubungi admin untuk info lebih lanjut.",
                            ]],
                        ]);
                    }),

                Tables\Actions\ViewAction::make(),
                ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
