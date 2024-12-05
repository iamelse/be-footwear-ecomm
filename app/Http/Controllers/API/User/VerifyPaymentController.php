<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;

class VerifyPaymentController extends Controller
{
    public function __construct()
    {
        // Set Midtrans Configuration
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$clientKey = env('MIDTRANS_CLIENT_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function verifyPayment(Request $request)
    {
        try {
            // Instantiate Notification
            $notification = new Notification();

            $transactionStatus = $notification->transaction_status;
            $paymentType = $notification->payment_type;
            $fraudStatus = $notification->fraud_status;
            $orderId = $notification->order_id;
            $grossAmount = $notification->gross_amount;
            $statusCode = $notification->status_code;
            $receivedSignature = $notification->signature_key;

            // Generate Signature
            $serverKey = env('MIDTRANS_SERVER_KEY');
            $generatedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            // Log details for debugging
            Log::info('Midtrans Notification:', [
                'transaction_status' => $transactionStatus,
                'payment_type' => $paymentType,
                'fraud_status' => $fraudStatus,
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
                'status_code' => $statusCode,
                'received_signature' => $receivedSignature,
                'generated_signature' => $generatedSignature,
            ]);

            // Verify the signature
            if ($generatedSignature !== $receivedSignature) {
                Log::error('Invalid signature for payment verification.', [
                    'order_id' => $orderId,
                    'generated_signature' => $generatedSignature,
                    'received_signature' => $receivedSignature,
                ]);
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            // Find the order
            $order = Order::findOrFail($orderId);
            Log::info('ORDER ID: ' . $orderId);

            // Update order based on transaction status
            switch ($transactionStatus) {
                case 'capture':
                    if ($fraudStatus === 'accept') {
                        $order->status = 'paid';
                        Log::info("Order ID {$orderId} is successfully paid.");
                    } else if ($fraudStatus === 'challenge') {
                        $order->status = 'pending';
                        Log::warning("Order ID {$orderId} is under fraud review.");
                    }
                    break;
                case 'settlement':
                    $order->status = 'paid';
                    Log::info("Order ID {$orderId} has been settled.");
                    break;
                case 'pending':
                    $order->status = 'pending';
                    Log::info("Order ID {$orderId} is pending payment.");
                    break;
                case 'deny':
                    $order->status = 'failed';
                    Log::warning("Order ID {$orderId} payment was denied.");
                    break;
                case 'expire':
                    $order->status = 'expired';
                    Log::info("Order ID {$orderId} has expired.");
                    break;
                case 'cancel':
                    $order->status = 'cancelled';
                    Log::info("Order ID {$orderId} was cancelled.");
                    break;
                default:
                    Log::error("Unhandled transaction status for Order ID {$orderId}: {$transactionStatus}");
                    break;
            }

            // Save the updated order status
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