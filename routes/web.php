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

Route::get('/models/{filename}', function ($filename) {
    $path = storage_path('var/lib/data/public/models/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    $file = file_get_contents($path);
    return response($file, 200)
        ->header('Content-Type', mime_content_type($path))
        ->header('Access-Control-Allow-Origin', 'https://ptcsi.vercel.app')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept');
});
