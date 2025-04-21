<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        // Cek apakah status berubah menjadi approved
        if ($payment->isDirty('status') && $payment->status === 'approved') {
            $order = $payment->order;

            if ($order) {
                // Hitung total semua pembayaran yang disetujui
                $approvedTotal = $order->payments()->where('status', 'approved')->sum('amount');

                // Update kolom total_paid
                $order->total_paid = $approvedTotal;

                // Jika sudah lunas, ubah status order jadi "paid"
                if ($approvedTotal >= $order->total_price) {
                    $order->status = 'paid';
                }

                $order->save();
            }
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        //
    }
}
