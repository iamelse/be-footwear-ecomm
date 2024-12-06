<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;

class VerifyPaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$clientKey = env('MIDTRANS_CLIENT_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function verifyPayment()
    {
        try {
            $notification = new Notification();

            $transactionStatus = $notification->transaction_status;
            $paymentType = $notification->payment_type;
            $fraudStatus = $notification->fraud_status;
            $orderId = $notification->order_id;
            $grossAmount = $notification->gross_amount;
            $statusCode = $notification->status_code;
            $receivedSignature = $notification->signature_key;

            $serverKey = env('MIDTRANS_SERVER_KEY');
            $generatedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            if ($generatedSignature !== $receivedSignature) {
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            $order = Order::findOrFail($orderId);
            // Log::info('ORDER ID: ' . $orderId);

            switch ($transactionStatus) {
                case 'capture':
                    if ($fraudStatus === 'accept') {
                        $order->status = 'paid';
                    } else if ($fraudStatus === 'challenge') {
                        $order->status = 'pending';
                    }
                    break;
                case 'settlement':
                    $order->status = 'paid';
                    break;
                case 'pending':
                    $order->status = 'pending';
                    break;
                case 'deny':
                    $order->status = 'failed';
                    break;
                case 'expire':
                    $order->status = 'expired';
                    break;
                case 'cancel':
                    $order->status = 'cancelled';
                    break;
                default:
                    break;
            }

            $order->save();

            return response()->json(['message' => 'Payment status updated successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Payment verification failed:', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Payment verification failed'], 500);
        }
    }
}