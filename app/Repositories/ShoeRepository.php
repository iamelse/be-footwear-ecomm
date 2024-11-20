<?php

namespace App\Repositories;

use App\Interfaces\ShoeRepositoryInterface;
use App\Models\Shoe;

class ShoeRepository implements ShoeRepositoryInterface
{
    public function index($filters = [])
    {
        $dbDriver = env('DB_CONNECTION', 'mysql');

        $query = Shoe::with([
            'images' => fn($query) => $query->where('is_primary', true),
            'inventory',
        ])->withCount([
            'inventory as color_count' => fn($query) => $dbDriver === 'pgsql'
                ? $query->select('color')->distinct()
                : $query->distinct('color'),
        ]);

        if (!empty($filters['q'])) {
            $searchTerm = '%' . $filters['q'] . '%';

            $query->where(function ($query) use ($searchTerm, $dbDriver) {
                $query->where('name', $dbDriver === 'pgsql' ? 'ilike' : 'like', $searchTerm)
                    ->orWhere('slug', $dbDriver === 'pgsql' ? 'ilike' : 'like', $searchTerm)
                    ->orWhereRaw(
                        $dbDriver === 'pgsql'
                            ? "CAST(price AS TEXT) ILIKE ?"
                            : "CAST(price AS CHAR) LIKE ?",
                        [$searchTerm]
                    )
                    ->orWhere('description', $dbDriver === 'pgsql' ? 'ilike' : 'like', $searchTerm);
            });
        }

        if (!empty($filters['sort_by']) && !empty($filters['sort_order'])) {
            $sortBy = $filters['sort_by'];
            $sortOrder = strtolower($filters['sort_order']) === 'desc' ? 'desc' : 'asc';

            if (in_array($sortBy, ['name', 'slug', 'price', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        $perPage = $filters['limit'] ?? 10;

        return $query->paginate($perPage);
    }

    public function show(string $slug)
    {
        return Shoe::where('slug', $slug)->with('images', 'inventory')->first();
    }
}
