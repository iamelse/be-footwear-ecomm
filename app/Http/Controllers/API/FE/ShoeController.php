<?php

namespace App\Http\Controllers\API\FE;

use App\Classes\APIResponseClass;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShoeResource;
use App\Interfaces\ShoeRepositoryInterface;
use Exception;

class ShoeController extends Controller
{
    private ShoeRepositoryInterface $shoeRepositoryInterface;
    
    public function __construct(ShoeRepositoryInterface $shoeRepositoryInterface)
    {
        $this->shoeRepositoryInterface = $shoeRepositoryInterface;
    }

    public function index()
    {
        try {
            $data = $this->shoeRepositoryInterface->index();

            return APIResponseClass::sendResponse(ShoeResource::collection($data), '', 200);
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

            return APIResponseClass::sendResponse(new ShoeResource($shoe), '', 200);
        } catch (Exception $e) {
            return APIResponseClass::throw($e, 'Failed to fetch shoe details.', false);
        }
    }
}
