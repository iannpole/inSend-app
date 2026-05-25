<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private string $serverKey;
    private string $snapUrl;
    private string $apiUrl;
    private bool $isProduction;

    public function __construct()
    {
        $this->serverKey    = config('midtrans.server_key');
        $this->snapUrl      = config('midtrans.snap_url');
        $this->apiUrl       = config('midtrans.api_url');
        $this->isProduction = config('midtrans.is_production', false);
    }

    /**
     * Create Snap transaction for an order.
     * Returns ['token' => '...', 'redirect_url' => '...', 'order_id' => '...']
     */
    public function createTransaction(Order $order, User $user): array
    {
        if (empty($this->serverKey)) {
            throw new \RuntimeException('Midtrans server key belum dikonfigurasi. Set MIDTRANS_SERVER_KEY di .env');
        }

        $orderId = $order->order_number ?? (string) $order->_id;

        // Build item details
        $itemDetails = [];
        foreach ($order->items as $item) {
            $itemDetails[] = [
                'id'       => $item['product_id'],
                'price'    => (int) round($item['price']),
                'quantity' => $item['qty'],
                'name'     => mb_substr($item['name'], 0, 50), // Midtrans max 50 chars
            ];
        }

        // Add delivery fee as line item
        if (($order->delivery_fee ?? 0) > 0) {
            $itemDetails[] = [
                'id'       => 'DELIVERY',
                'price'    => (int) round($order->delivery_fee),
                'quantity' => 1,
                'name'     => 'Ongkos Kirim',
            ];
        }

        // Add discount as negative line item
        if (($order->discount_amount ?? 0) > 0) {
            $itemDetails[] = [
                'id'       => 'DISCOUNT',
                'price'    => -(int) round($order->discount_amount),
                'quantity' => 1,
                'name'     => 'Diskon' . ($order->promo_code ? " ({$order->promo_code})" : ''),
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) round($order->total_price),
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone ?? '',
            ],
            'enabled_payments' => config('midtrans.enabled_payments'),
            'expiry' => [
                'unit'     => 'minutes',
                'duration' => config('midtrans.expiry_duration', 1440),
            ],
            'callbacks' => [
                'finish' => config('app.url') . '/payment/finish',
            ],
        ];

        // Add shipping address if available
        $shipping = $order->shipping_address;
        if ($shipping && is_array($shipping)) {
            $payload['customer_details']['shipping_address'] = [
                'first_name'  => $shipping['recipient_name'] ?? $user->name,
                'phone'       => $shipping['phone'] ?? '',
                'address'     => $shipping['street'] ?? '',
                'city'        => $shipping['city'] ?? '',
                'postal_code' => $shipping['postal_code'] ?? '',
            ];
        }

        // Call Midtrans Snap API
        $response = Http::withBasicAuth($this->serverKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->snapUrl, $payload);

        if ($response->failed()) {
            Log::error('Midtrans Snap error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Gagal membuat transaksi pembayaran: ' . $response->body());
        }

        $data = $response->json();

        return [
            'token'        => $data['token'] ?? null,
            'redirect_url' => $data['redirect_url'] ?? null,
            'order_id'     => $orderId,
        ];
    }

    /**
     * Handle Midtrans notification webhook
     */
    public function handleNotification(array $notification): ?Order
    {
        $orderId          = $notification['order_id'] ?? null;
        $transactionStatus = $notification['transaction_status'] ?? null;
        $fraudStatus      = $notification['fraud_status'] ?? null;
        $paymentType      = $notification['payment_type'] ?? null;

        if (!$orderId || !$transactionStatus) {
            Log::warning('Midtrans notification missing required fields', $notification);
            return null;
        }

        // Verify signature
        if (!$this->verifySignature($notification)) {
            Log::warning('Midtrans notification signature mismatch', $notification);
            return null;
        }

        // Find order by order_number or payment_id
        $order = Order::where('order_number', $orderId)
            ->orWhere('payment_id', $orderId)
            ->first();

        if (!$order) {
            Log::warning('Order not found for Midtrans notification', ['order_id' => $orderId]);
            return null;
        }

        // Process status
        $updates = ['payment_method' => $paymentType ?? $order->payment_method];

        if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
            if ($fraudStatus === 'accept' || empty($fraudStatus)) {
                $updates['status']         = Order::STATUS_PAID;
                $updates['payment_status'] = Order::PAYMENT_PAID;
                $updates['paid_at']        = now();
            }
        } elseif ($transactionStatus === 'pending') {
            $updates['status']         = Order::STATUS_AWAITING_PAYMENT;
            $updates['payment_status'] = Order::PAYMENT_PENDING;
        } elseif (in_array($transactionStatus, ['deny', 'cancel'])) {
            $updates['payment_status'] = Order::PAYMENT_FAILED;
            $updates['status']         = Order::STATUS_CANCELLED;

            // Restore stock
            $this->restoreStock($order);
        } elseif ($transactionStatus === 'expire') {
            $updates['payment_status'] = Order::PAYMENT_EXPIRED;
            $updates['status']         = Order::STATUS_PAYMENT_EXPIRED;

            // Restore stock
            $this->restoreStock($order);
        } elseif ($transactionStatus === 'refund' || $transactionStatus === 'partial_refund') {
            $updates['payment_status'] = Order::PAYMENT_REFUNDED;
        }

        $order->update($updates);

        Log::info('Midtrans notification processed', [
            'order_id' => $orderId,
            'status'   => $transactionStatus,
            'updates'  => $updates,
        ]);

        return $order;
    }

    /**
     * Check transaction status from Midtrans
     */
    public function checkStatus(string $orderId): array
    {
        if (empty($this->serverKey)) {
            throw new \RuntimeException('Midtrans server key belum dikonfigurasi');
        }

        $response = Http::withBasicAuth($this->serverKey, '')
            ->get("{$this->apiUrl}/{$orderId}/status");

        if ($response->failed()) {
            throw new \RuntimeException('Gagal cek status pembayaran');
        }

        return $response->json();
    }

    /**
     * Verify Midtrans signature key
     */
    private function verifySignature(array $notification): bool
    {
        $orderId     = $notification['order_id'] ?? '';
        $statusCode  = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $signatureKey = $notification['signature_key'] ?? '';

        $expectedSignature = hash(
            'sha512',
            $orderId . $statusCode . $grossAmount . $this->serverKey
        );

        return $signatureKey === $expectedSignature;
    }

    /**
     * Restore stock for cancelled/expired orders
     */
    private function restoreStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = \App\Models\Product::where('_id', $item['product_id'])->first();
            if ($product) {
                $product->update([
                    'stock_quantity' => ($product->stock_quantity ?? 0) + $item['qty']
                ]);
            }
        }
    }
}
