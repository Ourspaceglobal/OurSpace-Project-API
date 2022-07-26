<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreApartmentLocationRequest;
use App\Models\Apartment;
use App\Models\ApartmentLocation;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ApartmentLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Apartment $apartment)
    {
        $location = $apartment->location;

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment location fetched successfully.')
            ->withData([
                'location' => $location,
            ])
            ->build();
    }

    /**
     * Store/Update the specified resource.
     *
     * @param StoreApartmentLocationRequest $request
     * @param Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreApartmentLocationRequest $request, Apartment $apartment)
    {
        $this->authorize('update', $apartment);

        $apartmentLocation = ApartmentLocation::query()->whereBelongsTo($apartment)->firstOrNew([]);
        $apartmentLocation->apartment_id = $apartment->id;
        $apartmentLocation->state_id = $request->state_id;
        $apartmentLocation->city_id = $request->city_id;
        $apartmentLocation->local_government_id = $request->local_government_id;
        $apartmentLocation->house_number = $request->house_number;
        $apartmentLocation->street = $request->street;
        $apartmentLocation->landmark = $request->landmark;
        $apartmentLocation->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment location stored successfully.')
            ->withData([
                'location' => $apartmentLocation->refresh(),
            ])
            ->build();
    }
}
