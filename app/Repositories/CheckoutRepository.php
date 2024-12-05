<?php

namespace App\Repositories;

use App\Classes\APIResponseClass;
use App\Interfaces\CheckoutRepositoryInterface;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Shoe;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CheckoutRepository implements CheckoutRepositoryInterface
{
    protected $midtransService;

    /**
     * CheckoutRepository constructor.
     * 
     * @param MidtransService $midtransService
     */
    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Handles the checkout process for cart items, including stock validation,
     * order creation, inventory deduction, and payment transaction creation.
     * 
     * @param array $cartItems
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkoutFromCart(array $cartItems)
    {
        DB::beginTransaction();

        try {
            $items = [];
            $totalPrice = 0;

            foreach ($cartItems as $cartItem) {
                $inventory = Inventory::where('id', $cartItem['inventory_id'])->first();

                if (!$inventory || $inventory->stock < $cartItem['quantity']) {
                    return APIResponseClass::sendResponse(
                        [],
                        'Insufficient stock for some items.',
                        400,
                        false
                    );
                }

                $totalPrice += $cartItem['quantity'] * $inventory->shoe->price;
                $items[] = $this->__mapCartItemToPaymentGateway($cartItem, $inventory);
            }

            $order = $this->__createOrder($items, $totalPrice);

            $response = $this->__deductInventory($items);
            if ($response['status'] !== 'success') {
                DB::rollBack();
                return APIResponseClass::sendResponse(
                    [],
                    $response['message'],
                    400,
                    false
                );
            }

            $paymentUrl = $this->midtransService->createTransaction($order->id, $totalPrice, $items);

            if (!$paymentUrl) {
                DB::rollBack();
                return APIResponseClass::sendResponse(
                    [],
                    'Failed to create payment transaction.',
                    500,
                    false
                );
            }

            DB::commit();

            return APIResponseClass::sendResponse(
                ['payment_url' => $paymentUrl],
                'Checkout successful, please complete your payment.',
                200,
                true
            );
        } catch (\Exception $e) {
            DB::rollBack();
            $message = isset($response['message']) && !empty($response['message']) ? $response['message'] : 'Checkout from cart failed.';
            return APIResponseClass::throw($e, $message);
        }
    }

    /**
     * Handles the checkout process for a single product, including product validation,
     * inventory check, order creation, inventory deduction, and payment transaction creation.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkoutFromProduct(Request $request)
    {
        DB::beginTransaction();

        try {
            $product = Shoe::find($request->product_id);
            if (!$product) {
                return APIResponseClass::sendResponse([], 'Product not found.', 404, false);
            }

            $inventory = Inventory::with(['size', 'color'])
                ->where('shoe_id', $product->id)
                ->where('size_id', $request->size)
                ->where('color_id', $request->color)
                ->first();

            if (!$inventory) {
                return APIResponseClass::sendResponse([], 'Selected size and color not available.', 400, false);
            }

            if ($inventory->stock < $request->quantity) {
                return APIResponseClass::sendResponse([], 'Insufficient stock for this product.', 400, false);
            }

            $totalPrice = $request->quantity * $product->price;

            $items = [
                $this->__mapProductToPaymentGateway($product, $inventory, $request->quantity)
            ];

            $order = $this->__createOrder($items, $totalPrice);

            $response = $this->__deductInventory($items);
            if ($response['status'] !== 'success') {
                DB::rollBack();
                return APIResponseClass::sendResponse([], $response['message'], 400, false);
            }

            $paymentUrl = $this->midtransService->createTransaction($order->id, $totalPrice, $items);

            if (!$paymentUrl) {
                DB::rollBack();
                return APIResponseClass::sendResponse([], 'Failed to create payment transaction.', 500, false);
            }

            DB::commit();

            return APIResponseClass::sendResponse(
                ['payment_url' => $paymentUrl],
                'Checkout successful, please complete your payment.',
                200,
                true
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return APIResponseClass::throw($e, 'Checkout process failed.');
        }
    }

    /**
     * Maps cart item data to the payment gateway format for processing.
     * 
     * @param array $cartItem
     * @param Inventory $inventory
     * @return array
     */
    protected function __mapCartItemToPaymentGateway(array $cartItem, Inventory $inventory)
    {
        return [
            'id' => $inventory->id,
            'price' => $inventory->shoe->price,
            'quantity' => $cartItem['quantity'],
            'name' => $inventory->shoe->name,
        ];
    }

    /**
     * Maps product data to the payment gateway format for processing.
     * 
     * @param Shoe $product
     * @param Inventory $inventory
     * @param int $quantity
     * @return array
     */
    protected function __mapProductToPaymentGateway(Shoe $product, Inventory $inventory, int $quantity)
    {
        return [
            'id' => $inventory->id,
            'price' => $product->price,
            'quantity' => $quantity,
            'name' => $product->name,
        ];
    }

    /**
     * Creates an order with the given items and total price.
     * 
     * @param array $items
     * @param float $totalPrice
     * @return Order
     */
    protected function __createOrder(array $items, float $totalPrice)
    {
        try {
            $order = new Order([
                'total' => $totalPrice,
                'status' => 'pending',
            ]);

            $order->user()->associate(Auth::user());
            $order->save();

            foreach ($items as $item) {
                $order->order_items()->create([
                    'inventory_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            return $order;
        } catch (\Exception $e) {
            APIResponseClass::rollback($e, 'Failed to create order.');
        }
    }

    /**
     * Deducts inventory stock for each item in the order.
     * 
     * @param array $items
     * @return array
     */
    protected function __deductInventory(array $items)
    {
        try {
            foreach ($items as $item) {
                DB::transaction(function () use ($item) {
                    $inventory = Inventory::where('id', $item['id'])->lockForUpdate()->first();
                    if (!$inventory || $inventory->stock < $item['quantity']) {
                        throw new \Exception('Insufficient stock for item ' . $item['name']);
                    }
                    $inventory->stock -= $item['quantity'];
                    $inventory->save();
                });
            }

            return ['status' => 'success'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}