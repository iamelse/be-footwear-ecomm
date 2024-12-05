<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface CheckoutRepositoryInterface
{
    public function checkoutFromCart(array $cartItems);
    public function checkoutFromProduct(Request $request);
}