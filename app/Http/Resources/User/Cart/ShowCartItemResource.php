<?php

namespace App\Http\Resources\User\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowCartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $shoe = $this->inventory->shoe;
        $imageUrl = $shoe && $shoe->images->first() ? $shoe->images->first()->image_url : null;

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'shoe' => [
                'id' => $this->inventory->shoe->id,
                'name' => $this->inventory->shoe->name,
                'slug' => $this->inventory->shoe->slug,
                'price' => $this->inventory->shoe->price,
                'total_price' => $this->quantity * $this->inventory->shoe->price,
                'image_url' => $imageUrl,
            ],
            'size' => [
                'id' => $this->inventory->size->id,
                'size_us' => $this->inventory->size->size_us,
                'size_eu' => $this->inventory->size->size_eu,
                'size_uk' => $this->inventory->size->size_uk,
            ],
            'color' => [
                'id' => $this->inventory->color->id,
                'name' => $this->inventory->color->name,
                'hex_code' => $this->inventory->color->hex_code,
            ],
            'stock' => $this->inventory->stock,
        ];
    }
}
