<?php

namespace App\Http\Controllers\User;

use App\Events\User\ApartmentVerification;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreApartmentAmenityRequest;
use App\Models\Amenity;
use App\Models\Apartment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ApartmentAmenityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Apartment $apartment)
    {
        $amenities = $apartment->amenities;

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment amenities fetched successfully.')
            ->withData([
                'amenities' => $amenities,
            ])
            ->build();
    }

    /**
     * Store/Update the specified resource.
     *
     * @param StoreApartmentAmenityRequest $request
     * @param Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreApartmentAmenityRequest $request, Apartment $apartment)
    {
        $this->authorize('update', $apartment);

        DB::beginTransaction();

        $apartment->amenities()->sync($request->data);

        event(new ApartmentVerification($apartment));

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment amenities stored successfully.')
            ->withData([
                'amenities' => $apartment->amenities,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Apartment $apartment
     * @param Amenity $amenity
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Apartment $apartment, Amenity $amenity)
    {
        $this->authorize('delete', $apartment);

        DB::beginTransaction();

        $apartment->amenities()->detach($amenity->id);

        event(new ApartmentVerification($apartment));

        DB::commit();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
