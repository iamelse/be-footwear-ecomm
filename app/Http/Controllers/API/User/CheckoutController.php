<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Interfaces\CheckoutRepositoryInterface;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected $checkoutRepositoryInterface;

    public function __construct(CheckoutRepositoryInterface $checkoutRepositoryInterface)
    {
        $this->checkoutRepositoryInterface = $checkoutRepositoryInterface;
    }

    public function checkoutFromProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required_if:source,product|exists:shoes,id',
            'quantity' => 'required|integer|min:1',
            'size' => 'required_if:source,product|exists:sizes,id',
            'color' => 'required_if:source,product|exists:colors,id',
        ]);

        $response = $this->checkoutRepositoryInterface->checkoutFromProduct($request);

        $responseArray = $response->getData(true);

        return $responseArray;
    }

    public function checkoutFromCart(array $cartItems) 
    {
        return response()->json(['message' => 'Hello']);
    }

    public function callback(Request $request)
    {
        
    }
}