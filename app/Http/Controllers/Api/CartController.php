<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\{Cart, CartItems, Product};
use App\Http\Resources\Api\CartItemsResource;

class CartController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $items = $cart->cartItems()->with('product.machine')->get();

        return CartItemsResource::collection($items);
    }

    public function addItem(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $item = $cart->cartItems()->where('product_id', $request->product_id)->first();

        if ($item) {
            // Tambah jumlah jika item sudah ada
            $item->quantity += $request->quantity;
            $item->save();
        } else {
            // Buat item baru
            $item = $cart->cartItems()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json(['message' => 'Item added to cart', 'item' => $item]);
    }

    public function updateItem(Request $request, $itemId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item = CartItems::where('id', $itemId)
            ->whereHas('cart', fn ($q) => $q->where('user_id', $user->id))
            ->firstOrFail();

        $item->quantity = $request->quantity;
        $item->save();

        return response()->json(['message' => 'Item updated', 'item' => $item]);
    }

    public function removeItem($itemId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $item = CartItems::where('id', $itemId)
            ->whereHas('cart', fn ($q) => $q->where('user_id', $user->id))
            ->firstOrFail();

        $item->delete();

        return response()->json(['message' => 'Item removed']);
    }

    public function updateShipping(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $request->validate([
            'shipping_method' => 'required|string',
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $cart->shipping_method = $request->shipping_method;
        $cart->save();

        return response()->json(['message' => 'Shipping method updated']);
    }
}
