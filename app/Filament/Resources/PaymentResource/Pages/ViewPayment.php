<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Image;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('order_id')->label('Order ID')->disabled(),
            TextInput::make('amount')->label('Jumlah')->disabled(),
            Image::make('proof')
                ->label('Bukti Pembayaran')
                ->disk('public')
                ->height('auto')
                ->extraAttributes(['style' => 'max-width: 100%; max-height: 400px;']),
        ];
    }
}
