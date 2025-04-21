<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiKey;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('Masuk middleware CheckApiKey');

        $apiKey = $request->header('X-API-KEY');
        \Log::info('X-API-KEY:', [$apiKey]);

        if (!$apiKey || !ApiKey::where('key', $apiKey)->exists()) {
            \Log::warning('API KEY salah atau tidak ditemukan');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
