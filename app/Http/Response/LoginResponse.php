<?php
namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Log;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        Log::info('Login berhasil:', ['user' => $request->user()]);
        return redirect()->intended(\Filament\Facades\Filament::getUrl());
    }
}
