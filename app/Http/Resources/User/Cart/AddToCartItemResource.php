<?php

namespace App\Http\Resources\User\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddToCartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price' => $this->inventory->shoe->price,
            'total_price' => $this->quantity * $this->inventory->shoe->price,
            'inventory' => [
                'id' => $this->inventory->id,
                'size' => [
                    'size_us' => $this->inventory->size->size_us,
                    'size_eu' => $this->inventory->size->size_eu,
                    'size_uk' => $this->inventory->size->size_uk,
                ],
                'color' => [
                    'name' => $this->inventory->color->name,
                    'hex_code' => $this->inventory->color->hex_code,
                ],
            ],
        ];
    }
}
