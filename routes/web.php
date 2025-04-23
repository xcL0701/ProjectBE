<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/tes-wa', function () {
    $response = Http::withHeaders([
        'Authorization' => env('WABLAS_API_KEY'),
        'Content-Type' => 'application/json',
    ])->post(env('WABLAS_URL'), [
        'data' => [[
            'phone' => '6281291120030',
            'message' => 'Halo dari Laravel!',
            'secret' => false,
            'retry' => false,
            'isGroup' => false,
        ]]
    ]);

    dd($response->body());
});

Route::middleware('cors')->get('/storage/models/{filename}', function ($filename) {
    $path = storage_path('app/public/models/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Content-Type' => 'model/gltf-binary',
    ]);
});
