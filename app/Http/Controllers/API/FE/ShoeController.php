<?php

namespace App\Http\Controllers\API\FE;

use App\Classes\APIResponseClass;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shoe\ShoeIndexResource;
use App\Http\Resources\Shoe\ShoeShowResource;
use App\Http\Resources\ShoeResource;
use App\Interfaces\ShoeRepositoryInterface;
use Exception;
use Illuminate\Http\Request;

class ShoeController extends Controller
{
    private ShoeRepositoryInterface $shoeRepositoryInterface;
    
    public function __construct(ShoeRepositoryInterface $shoeRepositoryInterface)
    {
        $this->shoeRepositoryInterface = $shoeRepositoryInterface;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['q', 'sort_by', 'sort_order', 'limit']);

            $data = $this->shoeRepositoryInterface->index($filters);

            $meta = [
                'total' => $data->total(),
                'count' => $data->count(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ];

            $links = [
                'first' => $data->url(1),
                'last' => $data->url($data->lastPage()),
                'prev' => $data->previousPageUrl(),
                'next' => $data->nextPageUrl(),
            ];

            return APIResponseClass::sendResponse(
                [
                    'data' => ShoeIndexResource::collection($data),
                    'meta' => $meta,
                    'links' => $links
                ],
                'Successfully fetched shoes data.',
                200
            );
        } catch (\Throwable $e) {
            return APIResponseClass::throw($e, 'Failed to fetch shoes data.');
        }
    }

    public function show(string $slug)
    {
        try {
            $shoe = $this->shoeRepositoryInterface->show($slug);

            if (!$shoe) {
                return APIResponseClass::sendResponse([], 'Shoe not found.', 404, false);
            }

            return APIResponseClass::sendResponse(new ShoeShowResource($shoe), '', 200);
        } catch (Exception $e) {
            return APIResponseClass::throw($e, 'Failed to fetch shoe details.', false);
        }
    }
}
