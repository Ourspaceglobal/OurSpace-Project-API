<?php

namespace App\Http\Controllers\Generic;

use App\Http\Controllers\Controller;
use App\Models\ApartmentDuration;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ApartmentDurationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $apartmentDurations = ApartmentDuration::query()
            ->select(['id', 'duration_in_days', 'period'])
            ->orderBy('duration_in_days')
            ->get();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment durations fetched successfully.')
            ->withData([
                'apartment_durations' => $apartmentDurations,
            ])
            ->build();
    }
}
