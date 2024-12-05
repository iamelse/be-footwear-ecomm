<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$clientKey = env('MIDTRANS_CLIENT_KEY');

        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);

        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create a transaction and get the Snap token.
     *
     * @param int $orderId
     * @param int $amount
     * @param array $itemDetails
     * @return string|null
     */
    public function createTransaction(int $orderId, int $amount, array $itemDetails): ?string
    {
        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => $amount,
        ];

        $user = Auth::user();

        $transaction = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => [
                'name' => $user->name,
                'email' => $user->email,
            ]
        ];

        try {
            $paymentUrl = Snap::createTransaction($transaction)->redirect_url;

            return $paymentUrl;
        } catch (\Exception $e) {
            Log::error('Midtrans Error: ' . $e->getMessage());
            return null;
        }
    }
}