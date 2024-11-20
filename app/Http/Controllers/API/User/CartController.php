<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Interfaces\CartRepositoryInterface;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function addToCart(Request $request, string $slug)
    {
        return $this->cartRepository->addToCart($request, $slug);
    }

    public function showCart()
    {
        return $this->cartRepository->showCart();
    }
}
