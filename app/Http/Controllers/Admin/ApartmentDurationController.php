<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreApartmentDurationRequest;
use App\Http\Requests\Admin\UpdateApartmentDurationRequest;
use App\Models\ApartmentDuration;
use Illuminate\Http\Response;
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
        $apartmentDurations = ApartmentDuration::withTrashed()
            ->orderBy('duration_in_days')
            ->withCount([
                'apartments',
            ])
            ->get();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment durations fetched successfully.')
            ->withData([
                'apartment_durations' => $apartmentDurations,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreApartmentDurationRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreApartmentDurationRequest $request)
    {
        $apartmentDuration = new ApartmentDuration();
        $apartmentDuration->period = $request->period;
        $apartmentDuration->duration_in_days = $request->duration_in_days;
        $apartmentDuration->save();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Apartment duration stored successfully.')
            ->withData([
                'apartment_duration' => $apartmentDuration,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param mixed $apartmentDuration
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($apartmentDuration)
    {
        $apartmentDuration = ApartmentDuration::withTrashed()
            ->withCount([
                'apartments',
            ])
            ->findOrFail($apartmentDuration);

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment duration fetched successfully.')
            ->withData([
                'apartment_duration' => $apartmentDuration,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateApartmentDurationRequest $request
     * @param \App\Models\ApartmentDuration $apartmentDuration
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateApartmentDurationRequest $request, ApartmentDuration $apartmentDuration)
    {
        $apartmentDuration->period = $request->period;
        $apartmentDuration->duration_in_days = $request->duration_in_days;
        $apartmentDuration->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment duration updated successfully.')
            ->withData([
                'apartment_duration' => $apartmentDuration,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ApartmentDuration $apartmentDuration
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(ApartmentDuration $apartmentDuration)
    {
        $apartmentDuration->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param \App\Models\ApartmentDuration $apartmentDuration
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restore(ApartmentDuration $apartmentDuration)
    {
        $apartmentDuration->restore();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment duration restored successfully.')
            ->withData([
                'apartment_duration' => $apartmentDuration,
            ])
            ->build();
    }
}
