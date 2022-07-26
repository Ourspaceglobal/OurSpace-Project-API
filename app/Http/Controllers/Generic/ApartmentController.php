<?php

namespace App\Http\Controllers\Generic;

use App\Events\User\ViewLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterApartmentRequest;
use App\Models\Apartment;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ApartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param FilterApartmentRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(FilterApartmentRequest $request)
    {
        $apartments = Apartment::query()
            ->verified()
            ->active()
            ->noActiveRentals()
            ->with([
                'category:id,name,slug',
                'subCategory:id,name,slug',
                'apartmentDuration:id,period,duration_in_days',
                'amenities:id,name',
                'location',
                'galleries.media',
            ])
            ->withCount([
                'rentals as stays',
                'reviews',
            ])
            ->when($request->search, function ($query, $search) {
                $query->fullText($search);
            })
            ->when($request->user, function ($query, $user) {
                $query->whereHas('user', fn($query) => $query->where('finder', $user)->orWhere('id', $user));
            })
            ->when($request->date_added, function ($query, $date) {
                $formattedDate = explode('/', $date);

                $month = head($formattedDate);
                $year = last($formattedDate);
                $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
            })
            ->when($request->categories, function ($query, $categories) {
                $categories = array_map('trim', explode(',', $categories));
                $query->whereHas('category', fn ($query) => $query->whereIn('id', $categories));
            })
            ->when($request->sub_categories, function ($query, $sub_categories) {
                $sub_categories = array_map('trim', explode(',', $sub_categories));
                $query->whereHas('subCategory', fn ($query) => $query->whereIn('id', $sub_categories));
            })
            ->when($request->amenities, function ($query, $amenities) {
                $amenities = array_map('trim', explode(',', $amenities));
                $query->whereHas('amenities', fn ($query) => $query->whereIn('amenity_id', $amenities));
            })
            ->when($request->price_range, function ($query, $priceRange) {
                $priceRange = explode(',', $priceRange);
                $query
                    ->when(
                        count($priceRange) === 2,
                        fn ($query) => $query->whereBetween('price', [head($priceRange), last($priceRange)]),
                        fn ($query) => $query->where('price', '>=', $priceRange)
                    );
            })
            ->when($request->ratings, function ($query, $ratings) {
                $ratings = array_map('trim', explode(',', $ratings));

                $checks = implode(',', array_fill(0, (new \SplFixedArray(count($ratings)))->count(), '?'));

                $query->whereRaw("ROUND(rating) IN ($checks)", $ratings);
            })
            ->when($request->available_date, function ($query, $available_date) {
                $query->whereJsonDoesntContain(
                    'unavailable_booking_dates',
                    now()->parse($available_date)->toDateString()
                );
            })
            ->when(
                !is_null($request->is_featured),
                fn ($query) => $query->when(
                    (bool) $request->is_featured,
                    fn ($query) => $query->featured(),
                    fn ($query) => $query->featured(false),
                ),
            )
            ->latest()
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartments fetched successfully.')
            ->withData([
                'apartments' => $apartments,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param mixed $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request, $apartment)
    {
        $apartment = Apartment::query()
            ->where(fn ($query) => $query->where('id', $apartment)->orWhere('slug', $apartment))
            ->verified()
            ->active()
            ->with([
                'category:id,name,slug',
                'subCategory:id,name,slug',
                'apartmentDuration:id,period,duration_in_days',
                'amenities:id,name',
                'galleries.media',
                'contact',
                'location',
                'reviews' => fn($query) => $query->approved()
                    ->latest()
                    ->with([
                        'user:id,first_name,last_name',
                    ]),
                'user' => fn($query) => $query
                    ->select([
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone_number',
                        'rating',
                        'created_at',
                    ])
                    ->with([
                        'apartments' => fn($query) => $query->active()
                            ->verified()
                            ->noActiveRentals()
                            ->where(
                                fn ($query) => $query->where('id', '!=', $apartment)
                                    ->where('slug', '!=', $apartment)
                            )
                            ->with([
                                'category:id,name,slug',
                                'apartmentDuration:id,period,duration_in_days',
                                'location',
                                'galleries.media',
                            ])
                            ->withCount([
                                'rentals as stays',
                            ])
                            ->limit(4)
                        ,
                        'receivedReviews' => fn($query) => $query->approved()
                            ->latest()
                            ->with([
                                'user:id,first_name,last_name',
                            ]),
                    ])
                ,
            ])
            ->withCount([
                'rentals as stays',
                'views',
            ])
            ->latest()
            ->firstOrFail();

        $apartment->makeVisible('unavailable_booking_dates');

        event(new ViewLogger($apartment, $request->user()));

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment fetched successfully.')
            ->withData([
                'apartment' => $apartment,
            ])
            ->build();
    }
}
