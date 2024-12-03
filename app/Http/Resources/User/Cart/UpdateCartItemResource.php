<?php

namespace App\Http\Resources\User\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateCartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price' => $this->inventory->shoe->price ?? 0,
            'total_price' => $this->calculateTotalPrice(),
            'inventory' => $this->transformInventory(),
            'stock' => $this->inventory->stock ?? 0,
        ];
    }

    /**
     * Calculate the total price of the cart item.
     *
     * @return float
     */
    protected function calculateTotalPrice(): float
    {
        $price = $this->inventory->shoe->price ?? 0;
        return $this->quantity * $price;
    }

    /**
     * Transform the inventory details.
     *
     * @return array<string, mixed>
     */
    protected function transformInventory(): array
    {
        $inventory = $this->inventory;

        return [
            'id' => $inventory->id ?? null,
            'size' => [
                'size_us' => $inventory->size->size_us ?? null,
                'size_eu' => $inventory->size->size_eu ?? null,
                'size_uk' => $inventory->size->size_uk ?? null,
                'size_cm' => $inventory->size->size_cm ?? null,
            ],
            'color' => [
                'name' => $inventory->color->name ?? null,
                'hex_code' => $inventory->color->hex_code ?? null,
            ],
        ];
    }
}