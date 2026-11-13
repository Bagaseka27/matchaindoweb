<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = !config('midtrans.is_sandbox');
        Config::$isSanitized  = config('midtrans.is_sanitized');
        Config::$is3ds        = config('midtrans.is_3ds');
    }

    /**
     * Webhook / Notification Handler dari Midtrans.
     *
     * Midtrans akan POST ke endpoint ini setiap ada perubahan status pembayaran.
     * Endpoint ini harus PUBLIC (tanpa auth:sanctum).
     *
     * URL yang didaftarkan di Midtrans Dashboard:
     *   https://yourdomain.com/api/midtrans/callback
     */
    public function callback(Request $request)
    {
        try {
            $notification = new Notification();

            $orderId           = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus       = $notification->fraud_status ?? null;
            $paymentType       = $notification->payment_type ?? null;

            Log::info('Midtrans Notification', [
                'order_id'           => $orderId,
                'transaction_status' => $transactionStatus,
                'fraud_status'       => $fraudStatus,
                'payment_type'       => $paymentType,
            ]);

            $transaksi = DB::table('transaksi')
                ->where('order_id', $orderId)
                ->first();

            if (!$transaksi) {
                Log::warning("Midtrans callback: order_id {$orderId} not found");
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Tentukan status pembayaran berdasarkan notifikasi Midtrans
            $paymentStatus = $this->resolvePaymentStatus($transactionStatus, $fraudStatus);

            $updateData = [
                'payment_status'       => $paymentStatus,
                'midtrans_payment_type' => $paymentType,
                'updated_at'           => now(),
            ];

            // Set paid_at jika status = paid
            if ($paymentStatus === 'paid' && !$transaksi->paid_at) {
                $updateData['paid_at'] = now();
            }

            DB::table('transaksi')
                ->where('order_id', $orderId)
                ->update($updateData);

            Log::info("Midtrans: order {$orderId} updated to {$paymentStatus}");

            return response()->json(['message' => 'OK']);

        } catch (\Exception $e) {
            Log::error('Midtrans callback error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Error processing notification'], 500);
        }
    }

    /**
     * Cek status pembayaran dari mobile (untuk polling).
     */
    public function status(Request $request, string $orderId)
    {
        $transaksi = DB::table('transaksi')
            ->where('order_id', $orderId)
            ->first();

        if (!$transaksi) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json([
            'data' => [
                'order_id'       => $transaksi->order_id,
                'payment_status' => $transaksi->payment_status,
                'payment_type'   => $transaksi->midtrans_payment_type,
                'total_bayar'    => (float) $transaksi->total_bayar,
                'paid_at'        => $transaksi->paid_at,
            ],
        ]);
    }

    /**
     * Resolve status pembayaran dari Midtrans ke status internal.
     */
    private function resolvePaymentStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        return match ($transactionStatus) {
            'capture' => ($fraudStatus === 'accept') ? 'paid' : 'pending',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny', 'cancel' => 'cancelled',
            'expire' => 'expired',
            'failure' => 'failed',
            default => 'pending',
        };
    }
}

