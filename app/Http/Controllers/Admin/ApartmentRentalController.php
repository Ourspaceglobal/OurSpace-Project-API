<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TerminateApartmentRentalRequest;
use App\Models\ApartmentRental;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\QueryBuilder;

class ApartmentRentalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $apartmentRentals = QueryBuilder::for(ApartmentRental::bookings(false))
            ->latest('expired_at')
            ->allowedIncludes([
                'user',
                'apartment',
                'paymentTransaction',
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment rentals fetched successfully.')
            ->withData([
                'apartment_rentals' => $apartmentRentals,
            ])
            ->build();
    }

    /**
     * Terminate the progress of the specified resource.
     *
     * @param TerminateApartmentRentalRequest $request
     * @param ApartmentRental $apartmentRental
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function terminate(TerminateApartmentRentalRequest $request, ApartmentRental $apartmentRental)
    {
        abort_if(
            !$apartmentRental->is_active,
            Response::HTTP_EXPECTATION_FAILED,
            'Apartment Rental is not active, hence cannot be terminated.'
        );

        $apartmentRental->termination_reason = $request->reason;
        $apartmentRental->terminated_at = now();
        $apartmentRental->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment rental terminated successfully.')
            ->withData([
                'apartment_rental' => $apartmentRental,
            ])
            ->build();
    }
}
