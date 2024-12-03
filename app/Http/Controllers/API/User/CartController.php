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

    public function store(Request $request, string $slug)
    {
        return $this->cartRepository->addToCart($request, $slug);
    }

    public function index()
    {
        return $this->cartRepository->showCart();
    }

    public function update(Request $request, string $slug)
    {
        return $this->cartRepository->updateCart($request, $slug);
    }

    public function destroy(Request $request, string $slug)
    {
        return $this->cartRepository->removeCart($request, $slug);
    }
}
