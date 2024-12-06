<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Notification;

class VerifyPaymentController extends Controller
{
    public function verifyPayment()
    {
        DB::beginTransaction();

        try {
            $params = $this->__extractNotificationParams();

            if (!$this->__verifySignature($params)) {
                return $this->__sendResponse([], 'Invalid signature', 403, false);
            }

            $order = $this->__fetchOrder($params['order_id']);

            $updatedStatus = $this->__mapTransactionStatus($params);
            $order->status = $updatedStatus;
            $order->save();

            DB::commit();

            return $this->__sendResponse([], 'Payment status updated successfully', 200, true);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment verification failed:', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            return $this->__sendResponse([], 'Payment verification failed', 500, false);
        }
    }

    /**
     * Extract notification parameters from Midtrans notification.
     *
     * @return array
     */
    private function __extractNotificationParams(): array
    {
        $notification = new Notification();

        return [
            'transaction_status' => $notification->transaction_status,
            'fraud_status' => $notification->fraud_status,
            'order_id' => $notification->order_id,
            'gross_amount' => $notification->gross_amount,
            'status_code' => $notification->status_code,
            'received_signature' => $notification->signature_key,
        ];
    }

    /**
     * Verify the signature from Midtrans.
     *
     * @param array $params
     * @return bool
     */
    private function __verifySignature(array $params): bool
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $generatedSignature = hash('sha512', $params['order_id'] . $params['status_code'] . $params['gross_amount'] . $serverKey);

        return $generatedSignature === $params['received_signature'];
    }

    /**
     * Fetch the order by ID.
     *
     * @param int $orderId
     * @return Order
     */
    private function __fetchOrder(int $orderId): Order
    {
        return Order::findOrFail($orderId);
    }

    /**
     * Map transaction status to an order status.
     *
     * @param array $params
     * @return string
     */
    private function __mapTransactionStatus(array $params): string
    {
        return match ($params['transaction_status']) {
            'capture' => $params['fraud_status'] === 'accept' ? 'paid' : 'pending',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'expire' => 'expired',
            'cancel' => 'cancelled',
            default => 'unknown',
        };
    }

    /**
     * Send a response in a standard format.
     *
     * @param array $data
     * @param string $message
     * @param int $statusCode
     * @param bool $success
     * @return \Illuminate\Http\JsonResponse
     */
    private function __sendResponse(array $data, string $message, int $statusCode, bool $success)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}