<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\{Cart, CartItems, Order, OrderItems, OrderLink, Payment, Product};
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
        ]);

        DB::beginTransaction();

        try {
            $path = $request->file('proof')->store('proofs', 'public');

            $payment = Payment::create([
                'order_id' => $request->order_id,
                'amount' => $request->amount,
                'proof' => $path,
                'status' => 'pending', // <- auto-approve langsung
                'paid_at' => now(),
            ]);

            $order = Order::find($request->order_id);

            // Hitung ulang total yang sudah dibayar
            $approvedTotal = $order->payments()->where('status', 'approved')->sum('amount');

            if ($approvedTotal >= $order->total_price) {
                $order->status = 'paid';
                $order->save();
            }

            DB::commit();

            return response()->json(['message' => 'Payment uploaded', 'payment' => $payment]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan saat mengupload pembayaran.'], 500);
        }
    }
    public function show(string $token)
    {
        $orderLink = OrderLink::where('token', $token)->first();

        if (!$orderLink) {
            return response()->json(['message' => 'Token tidak ditemukan'], 404);
        }

        if ($orderLink->used) {
            return response()->json(['message' => 'Token sudah digunakan'], 410); // Gone
        }

        $order = Order::with('orderItems.product')->find($orderLink->order_id);

        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        return response()->json([
            'order' => $order,
            'items' => $order->orderItems,
        ]);
    }
}
