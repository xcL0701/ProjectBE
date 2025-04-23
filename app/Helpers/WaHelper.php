<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class WaHelper
{
    public static function sendMessage(string $phone, string $message): void
    {
        $response = Http::withHeaders([
            'Authorization' => config('services.wablas.token'),
        ])->post(config('services.wablas.url'), [
            'phone' => $phone,
            'message' => $message,
            'isGroup' => false,
        ]);

        if ($response->failed()) {
            \Log::error('Wablas Send Failed: ' . $response->body());
        }
    }
}
