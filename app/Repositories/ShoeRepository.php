<?php

namespace App\Repositories;

use App\Interfaces\ShoeRepositoryInterface;
use App\Models\Shoe;

class ShoeRepository implements ShoeRepositoryInterface
{
    public function index()
    {
        return Shoe::with(['images' => function($query) {
            $query->where('is_primary', true);
        }])->get();
    }

    public function show(string $slug)
    {
        return Shoe::where('slug', $slug)->with('images')->first();
    }
}
