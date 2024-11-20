<?php

namespace App\Http\Resources\Shoe;

use App\Http\Resources\ShoeImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShoeIndexResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'brand_name' => $this->whenLoaded('brand') ? $this->brand->name : null,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'color_count' => $this->whenLoaded('inventory') ? $this->inventory->count() : 0,
            'primary_image' => $this->whenLoaded('images', function () {
                return $this->images->firstWhere('is_primary', true) ?? ['image_url' => 'https://via.placeholder.com/400x400.png/003322?text=default'];
            }),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}