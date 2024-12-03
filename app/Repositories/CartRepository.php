<?php

namespace App\Repositories;

use App\Classes\APIResponseClass;
use App\Http\Resources\User\Cart\AddToCartItemResource;
use App\Http\Resources\User\Cart\ShowCartItemResource;
use App\Http\Resources\User\Cart\UpdateCartItemResource;
use App\Interfaces\CartRepositoryInterface;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Shoe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartRepository implements CartRepositoryInterface
{
    public function addToCart(Request $request, string $slug)
    {
        $request->validate([
            'size' => 'required|integer|exists:sizes,id',
            'color' => 'required|integer|exists:colors,id',
            'quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $product = Shoe::where('slug', $slug)->first();
            if (!$product) {
                return APIResponseClass::sendResponse([], 'Product not found.', 404, false);
            }

            $inventory = Inventory::with(['size', 'color'])
                ->where('shoe_id', $product->id)
                ->where('size_id', $request->size)
                ->where('color_id', $request->color)
                ->first();

            if (!$inventory) {
                return APIResponseClass::sendResponse(null, 'Selected size and color not available.', 400, false);
            }

            $cart = Auth::user()->carts->first() ?? Cart::create(['user_id' => Auth::id()]);

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('inventory_id', $inventory->id)
                ->first();

            $currentQuantity = $cartItem ? $cartItem->quantity : 0;
            $requestedQuantity = $currentQuantity + 1;

            if ($inventory->stock < $requestedQuantity) {
                return APIResponseClass::sendResponse(null, 'Insufficient stock for the requested quantity.', 400, false);
            }

            if ($cartItem) {
                $cartItem->increment('quantity');
            } else {
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'inventory_id' => $inventory->id,
                    'quantity' => 1,
                ]);
            }

            DB::commit();

            return APIResponseClass::sendResponse(
                new AddToCartItemResource($cartItem),
                'Item added to cart successfully.',
                200,
                true
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return APIResponseClass::throw($e, 'Failed to add item to cart.');
        }
    }

    public function showCart()
    {
        try {
            $user = Auth::user();

            $cart = $user->carts->first();

            if (!$cart) {
                return APIResponseClass::sendResponse([], 'No cart found for this user.', 404, false);
            }

            $cartItems = CartItem::with(['inventory.shoe', 'inventory.size', 'inventory.color', 'inventory.shoe.images'])
                                ->where('cart_id', $cart->id)
                                ->get();

            if ($cartItems->isEmpty()) {
                return APIResponseClass::sendResponse([], 'No items in the cart.', 404, false);
            }

            return APIResponseClass::sendResponse(
                ShowCartItemResource::collection($cartItems),
                'Cart retrieved successfully.'
            );

        } catch (\Exception $e) {
            return APIResponseClass::throw($e, 'Failed to retrieve cart items.');
        }
    }

    public function updateCart(Request $request, string $slug)
    {
        $request->validate([
            'size' => 'required|integer|exists:sizes,id',
            'color' => 'required|integer|exists:colors,id',
            'quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $product = Shoe::where('slug', $slug)->first();
            if (!$product) {
                return APIResponseClass::sendResponse([], 'Product not found.', 404, false);
            }

            $inventory = Inventory::with(['size', 'color'])
                ->where('shoe_id', $product->id)
                ->where('size_id', $request->size)
                ->where('color_id', $request->color)
                ->first();

            if (!$inventory) {
                return APIResponseClass::sendResponse(null, 'Selected size and color not available.', 400, false);
            }

            $cart = Auth::user()->carts->first() ?? Cart::create(['user_id' => Auth::id()]);

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('inventory_id', $inventory->id)
                ->first();

            if (!$cartItem) {
                return APIResponseClass::sendResponse(null, 'Cart item not found.', 404, false);
            }

            if ($inventory->stock < $request->quantity) {
                return APIResponseClass::sendResponse(null, 'Insufficient stock for the requested quantity.', 400, false);
            }

            $cartItem->update(['quantity' => $request->quantity]);

            DB::commit();

            return APIResponseClass::sendResponse(
                new UpdateCartItemResource($cartItem),
                'Quantity updated successfully.',
                200,
                true
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return APIResponseClass::throw($e, 'Failed to update cart quantity.');
        }
    }

    public function removeCart(Request $request, string $slug)
    {
        $request->validate([
            'size' => 'required|integer|exists:sizes,id',
            'color' => 'required|integer|exists:colors,id',
        ]);

        DB::beginTransaction();

        try {
            $product = Shoe::where('slug', $slug)->first();
            if (!$product) {
                return APIResponseClass::sendResponse([], 'Product not found.', 404, false);
            }

            $inventory = Inventory::with(['size', 'color'])
                            ->where('shoe_id', $product->id)
                            ->where('size_id', $request->size)
                            ->where('color_id', $request->color)
                            ->first();

            if (!$inventory) {
                return APIResponseClass::sendResponse([], 'Inventory item not found.', 404, false);
            }

            $cart = Auth::user()->carts->first();

            if (!$cart) {
                return APIResponseClass::sendResponse([], 'Cart not found.', 404, false);
            }

            $cartItem = CartItem::where('cart_id', $cart->id)
                                ->where('inventory_id', $inventory->id)
                                ->first();

            if (!$cartItem) {
                return APIResponseClass::sendResponse([], 'Cart item not found.', 404, false);
            }

            $cartItem->delete();

            DB::commit();

            return APIResponseClass::sendResponse([], 'Cart item removed successfully.', 200, true);

        } catch (\Exception $e) {
            DB::rollBack();
            return APIResponseClass::throw($e, 'Failed to remove item from cart.');
        }
    }
}