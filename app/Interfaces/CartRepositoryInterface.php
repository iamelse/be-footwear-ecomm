<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface CartRepositoryInterface
{
    public function addToCart(Request $request, string $slug);
    public function showCart();
    //public function removeItem($cartItemId);
    //public function updateQuantity(Request $request, $cartItemId);
}
