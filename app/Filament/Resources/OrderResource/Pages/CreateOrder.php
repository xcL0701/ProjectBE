<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use App\Models\OrderItems;
use App\Models\OrderLink;
use App\Models\Cart;
use App\Models\CartItems;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $orderItems = $data['order_items'] ?? [];

        $totalItemPrice = collect($orderItems)->sum(fn ($item) => (int) $item['unit_price'] * (int) $item['quantity']);
        $ongkir = (int) ($data['shipping_cost'] ?? 0);

        $data['total_price'] = $totalItemPrice + $ongkir;
        $data['id'] = $data['id'] ?? 'CSIORD-' . strtoupper(Str::random(8));

        return $data;
    }

    protected function afterCreate(): void
    {
        OrderLink::create([
            'order_id' => $this->record->id,
            'token' => Str::uuid(),
        ]);
        if ($this->record->user_id) {
            CartItems::whereHas('cart', function ($query) {
                $query->where('user_id', $this->record->user_id);
            })->delete();

            Cart::where('user_id', $this->record->user_id)->delete();
        }
    }
    public function getTitle(): string
    {
        return 'Tambah Pesanan Baru';
    }

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
