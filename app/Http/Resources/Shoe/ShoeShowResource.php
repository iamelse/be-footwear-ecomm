<?php

namespace App\Http\Resources\Shoe;

use App\Http\Resources\ShoeImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShoeShowResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $colors = $this->inventory->pluck('color')->unique();
        $sizes = $this->inventory->pluck('size')->unique();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'description' => $this->description,
            'images' => ShoeImageResource::collection($this->whenLoaded('images')),
            'colors' => $colors,
            'sizes' => $sizes,
        ];
    }
}
