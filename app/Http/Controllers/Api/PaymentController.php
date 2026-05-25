<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * POST /api/payment/callback — Midtrans webhook (NO AUTH)
     *
     * Midtrans sends server-to-server notification.
     * This route must be publicly accessible.
     */
    public function callback(Request $request): JsonResponse
    {
        $notification = $request->all();

        Log::info('Midtrans webhook received', $notification);

        try {
            $order = $this->paymentService->handleNotification($notification);

            if (!$order) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Order tidak ditemukan atau signature tidak valid',
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Notifikasi berhasil diproses',
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans webhook error', [
                'error' => $e->getMessage(),
                'data'  => $notification,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses notifikasi',
            ], 500);
        }
    }

    /**
     * GET /api/orders/{id}/payment-status — Check payment status (AUTH)
     */
    public function paymentStatus(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        // Verify ownership
        if (!$request->user()->isAdmin() &&
            (string) $order->user_id !== (string) $request->user()->_id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden',
            ], 403);
        }

        $data = [
            'order_id'       => (string) $order->_id,
            'order_number'   => $order->order_number,
            'status'         => $order->status,
            'payment_status' => $order->payment_status ?? 'pending',
            'payment_method' => $order->payment_method,
            'payment_url'    => $order->payment_url,
            'payment_token'  => $order->payment_token,
            'total_price'    => $order->total_price,
            'paid_at'        => $order->paid_at?->toIso8601String(),
        ];

        // Optionally check real-time status from Midtrans
        if ($order->order_number && $order->payment_status !== Order::PAYMENT_PAID) {
            try {
                $midtransStatus = $this->paymentService->checkStatus($order->order_number);
                $data['midtrans_status'] = $midtransStatus['transaction_status'] ?? null;
            } catch (\Exception $e) {
                // Midtrans not configured or unreachable
                $data['midtrans_status'] = null;
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * POST /api/orders/{id}/pay — Initiate/retry payment for pending order (AUTH)
     */
    public function pay(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        // Verify ownership
        if ((string) $order->user_id !== (string) $request->user()->_id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden',
            ], 403);
        }

        // Only allow payment for pending/awaiting_payment orders
        if (!in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_AWAITING_PAYMENT, Order::STATUS_PAYMENT_EXPIRED])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Order ini tidak bisa dibayar (status: ' . $order->status . ')',
            ], 422);
        }

        // If already has payment URL and not expired, return existing
        if ($order->payment_url && $order->payment_status === Order::PAYMENT_PENDING) {
            return response()->json([
                'status'       => 'success',
                'message'      => 'Gunakan link pembayaran yang sudah ada',
                'payment_url'  => $order->payment_url,
                'payment_token'=> $order->payment_token,
            ]);
        }

        // Create new Midtrans transaction
        try {
            $result = $this->paymentService->createTransaction($order, $request->user());

            $order->update([
                'status'        => Order::STATUS_AWAITING_PAYMENT,
                'payment_status'=> Order::PAYMENT_PENDING,
                'payment_url'   => $result['redirect_url'] ?? null,
                'payment_token' => $result['token'] ?? null,
                'payment_id'    => $result['order_id'] ?? null,
            ]);

            return response()->json([
                'status'        => 'success',
                'message'       => 'Link pembayaran berhasil dibuat',
                'payment_url'   => $result['redirect_url'] ?? null,
                'payment_token' => $result['token'] ?? null,
                'data'          => new OrderResource($order),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }
}
