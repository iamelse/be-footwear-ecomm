<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class VerifyPaymentController extends Controller
{
    public function verifyPayment(Request $request)
    {
        // Extract relevant data from the webhook request
        $orderId = $request->order_id;
        $statusCode = $request->status_code;
        $grossAmount = $request->amount;
        $transactionStatus = $request->transaction_status;
        $signature = $request->signature_key;

        // Generate the signature from the webhook data
        $generatedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . env('MIDTRANS_SERVER_KEY'));

        // Check if the signature matches the one sent by Midtrans
        if ($signature !== $generatedSignature) {
            Log::error('Invalid signature for payment verification.', [
                'order_id' => $orderId,
                'generated_signature' => $generatedSignature,
                'received_signature' => $signature
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid signature, payment verification failed.'
            ], 400);
        }

        // Fetch the order from the database
        $order = Order::find($orderId);
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.'
            ], 404);
        }

        // Handle the payment status
        switch ($transactionStatus) {
            case 'capture':
                if ($statusCode == '200') {
                    // Payment is successful, update order status to paid
                    $order->status = 'paid';
                    $order->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Payment captured successfully.'
                    ]);
                } else {
                    // Payment failed
                    $order->status = 'failed';
                    $order->save();

                    return response()->json([
                        'success' => false,
                        'message' => 'Payment failed.'
                    ], 400);
                }

            case 'pending':
                // Handle pending payment (e.g., waiting for payment confirmation)
                $order->status = 'pending';
                $order->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment is pending.'
                ]);

            case 'expire':
                // Handle expired payment
                $order->status = 'expired';
                $order->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Payment has expired.'
                ], 400);

            default:
                // Handle unexpected status
                return response()->json([
                    'success' => false,
                    'message' => 'Unexpected payment status.'
                ], 400);
        }
    }
}