<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ApartmentFavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $apartments = $request->user()->favorites()
            ->with([
                'category:id,name,slug',
                'subCategory:id,name,slug',
                'apartmentDuration:id,duration_in_days,period',
                'galleries.media',
                'amenities:id,name',
            ])
            ->withCount([
                'rentals as stays',
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('User apartment favorites fetched successfully.')
            ->withData([
                'apartments' => $apartments,
            ])
            ->build();
    }

    /**
     * Store/Update the specified resource.
     *
     * @param Request $request
     * @param Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request, Apartment $apartment)
    {
        $user = $request->user();
        $term = 'added to';

        if ($user->favorites()->where('apartment_id', $apartment->id)->exists()) {
            $term = 'removed from';
            $user->favorites()->detach($apartment->id);
        } else {
            $user->favorites()->attach($apartment->id);
        }

        return ResponseBuilder::asSuccess()
            ->withMessage("Apartment {$term} user favorites.")
            ->withData([
                'apartment' => $apartment,
            ])
            ->build();
    }
}
