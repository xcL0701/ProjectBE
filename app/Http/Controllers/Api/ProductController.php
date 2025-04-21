<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['machine', 'productPhotos', 'likes'])->get();

        $user = Auth::user();

        $products = $products->map(function ($product) use ($user) {
            return [
                ...$product->toArray(),
                'likes_count' => $product->likes->count(),
                'liked_by_user' => $user ? $product->likes->contains('user_id', $user->id) : false,
            ];
        });

        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with(['machine', 'productPhotos', 'likes'])->findOrFail($id);

        $user = Auth::user();

        return response()->json([
            ...$product->toArray(),
            'likes_count' => $product->likes->count(),
            'liked_by_user' => $user ? $product->likes->contains('user_id', $user->id) : false,
        ]);
    }
}
