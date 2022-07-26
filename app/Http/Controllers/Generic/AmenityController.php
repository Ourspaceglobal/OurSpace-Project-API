<?php

namespace App\Http\Controllers\Generic;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class AmenityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $amenities = Amenity::active()
            ->select(['id', 'name', 'is_primary', 'is_active'])
            ->orderBy('is_primary')
            ->get();

        return ResponseBuilder::asSuccess()
            ->withMessage('Amenities fetched successfully.')
            ->withData([
                'amenities' => $amenities,
            ])
            ->build();
    }
}
