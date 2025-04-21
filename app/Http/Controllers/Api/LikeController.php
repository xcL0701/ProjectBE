<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Product;

class LikeController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = $request->user();
        $productId = $request->product_id;

        $like = Like::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            Like::create([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
            $liked = true;
        }

        $likesCount = Like::where('product_id', $productId)->count();

        return response()->json([
            'liked' => $liked,
            'likes_count' => $likesCount,
        ]);
    }
    public function index(Request $request)
    {
        $user = $request->user();

        // Ambil semua produk yang disukai user, termasuk relasi 'machine'
        $likedProducts = $user->likedProducts()->with('machine')->get();

        return response()->json([
            'data' => $likedProducts
        ]);
    }
}
