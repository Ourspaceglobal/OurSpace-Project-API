<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApartmentRental;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\QueryBuilder;

class ApartmentBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $apartmentBookings = QueryBuilder::for(ApartmentRental::bookings())
            ->allowedIncludes([
                'user',
                'apartment',
                'paymentTransaction',
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment bookings fetched successfully.')
            ->withData([
                'apartment_bookings' => $apartmentBookings,
            ])
            ->build();
    }
}
