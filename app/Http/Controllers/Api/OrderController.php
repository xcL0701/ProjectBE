<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\{Order, OrderItems, OrderLink, Payment};
use App\Http\Resources\Api\OrderResource;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $orders = Order::with(['orderItems.product:id,name,thumbnail'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at_local,
                    'order_items' => $order->orderItems->map(function ($item) {
                        return [
                            'product' => [
                                'name' => $item->product->name,
                                'thumbnail' => $item->product->thumbnail,
                            ]
                        ];
                    }),
                ];
            }),
        ]);
    }

    public function myOrders()
    {
        $orders = Order::with(['orderItems.product', 'payments'])
            ->where('user_id', Auth::id())
            ->get();

        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::with(['orderItems.product', 'payments']) // eager load orderItems & payments
            ->findOrFail($id);

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    public function createFromCart(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|integer|min:0',
            'shipping_method' => 'required|in:pickup,delivery',
            'shipping_cost' => 'nullable|integer|min:0',
            'note' => 'nullable|string',
        ]);

        $orderId = 'CSIORD-' . strtoupper(Str::random(8));
        $shippingCost = $request->shipping_cost ?? 0;
        $totalProductPrice = collect($request->items)->sum(fn($item) => $item['unit_price'] * $item['quantity']);
        $totalPrice = $totalProductPrice + $shippingCost;

        $order = Order::create([
            'id' => $orderId,
            'user_id' => $request->user_id,
            'shipping_method' => $request->shipping_method,
            'shipping_cost' => $shippingCost,
            'note' => $request->note,
            'total_price' => $totalPrice,
            'total_paid' => 0,
            'status' => 'pending',
        ]);

        foreach ($request->items as $item) {
            OrderItems::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['unit_price'] * $item['quantity'],
            ]);
        }

        $token = Str::uuid();
        OrderLink::create([
            'order_id' => $order->id,
            'token' => $token,
            'used' => false,
        ]);

        return response()->json([
            'message' => 'Order created',
            'order_id' => $order->id,
            'link' => url("/payment/confirmation/{$token}"),
        ]);
    }

    public function showByToken($token)
    {
        $link = OrderLink::where('token', $token)->firstOrFail();

        if ($link->used) {
            return response()->json(['message' => 'Link sudah digunakan.'], 410);
        }

        $order = Order::with('orderItems.product')->findOrFail($link->order_id);

        return response()->json([
            'order' => $order,
            'items' => $order->orderItems,
        ]);
    }

    public function markTokenUsed($token)
    {
        $link = OrderLink::where('token', $token)->first();
        if ($link) {
            $link->used = true;
            $link->save();
        }

        return response()->json(['message' => 'Token marked as used']);
    }

    public function payments($id)
    {
        $order = Order::with('payments')->findOrFail($id);
        return response()->json(['payments' => $order->payments]);
    }
}
