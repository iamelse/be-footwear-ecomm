<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface CartRepositoryInterface
{
    public function addToCart(Request $request, string $slug);
    public function showCart();
    public function updateCart(Request $request, string $slug);
    public function removeCart(Request $request, string $slug);
}