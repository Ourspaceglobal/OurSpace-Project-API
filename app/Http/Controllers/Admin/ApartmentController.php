<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\QueryBuilder\AddRelationExtract;
use App\Http\Controllers\Controller;
use App\Models\Apartment;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class ApartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $apartments = QueryBuilder::for(Apartment::class)
            ->allowedFilters([
                AllowedFilter::trashed(),
                'is_active',
                'is_verified',
                'is_featured',
            ])
            ->allowedIncludes([
                'user',
                'category',
                'subCategory',
                'apartmentDuration',
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'name',
                'price',
                'is_verified',
                'is_featured',
                'is_active',
                'updated_at',
                'created_at',
            ])
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
     * @param mixed $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($apartment)
    {
        $apartment = QueryBuilder::for(
            Apartment::query()->withTrashed()->where('id', $apartment)->orWhere('slug', $apartment)
        )
            ->allowedIncludes([
                'user',
                'category',
                'subCategory',
                'apartmentDuration',
                'amenities',
                AllowedInclude::custom('galleries', new AddRelationExtract([
                    'media',
                ])),
                'contact',
                'location',
                AllowedInclude::custom('bookings', new AddRelationExtract([
                    'user:id,first_name,last_name,email,phone_number',
                ])),
                AllowedInclude::custom('rentals', new AddRelationExtract([
                    'user:id,first_name,last_name,email,phone_number',
                ])),
                'customApartmentKycs',
            ])
            ->firstOrFail();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment fetched successfully.')
            ->withData([
                'apartment' => $apartment,
            ])
            ->build();
    }

    /**
     * Toggle active status of the specified resource in storage.
     *
     * @param \App\Models\Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleActiveStatus(Apartment $apartment)
    {
        $apartment->is_active = !$apartment->is_active;
        $apartment->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment active status updated successfully.')
            ->withData([
                'apartment' => $apartment,
            ])
            ->build();
    }

    /**
     * Toggle verified status of the specified resource in storage.
     *
     * @param \App\Models\Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleVerifiedStatus(Apartment $apartment)
    {
        $apartment->is_verified = !$apartment->is_verified;
        $apartment->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment verified status updated successfully.')
            ->withData([
                'apartment' => $apartment,
            ])
            ->build();
    }

    /**
     * Toggle featured status of the specified resource in storage.
     *
     * @param \App\Models\Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleFeaturedStatus(Apartment $apartment)
    {
        $apartment->is_featured = !$apartment->is_featured;
        $apartment->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment featured status updated successfully.')
            ->withData([
                'apartment' => $apartment,
            ])
            ->build();
    }
}
