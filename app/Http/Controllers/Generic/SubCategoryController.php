<?php

namespace App\Http\Controllers\Generic;

use App\Contracts\QueryBuilder\BasicInfoExtract;
use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $subCategories = QueryBuilder::for(SubCategory::active())
            ->select(['id', 'category_id', 'name', 'slug', 'description'])
            ->allowedIncludes([
                AllowedInclude::custom('category', new BasicInfoExtract([
                    'id',
                    'name',
                    'slug',
                ])),
            ])
            ->orderBy('name')
            ->get();

        return ResponseBuilder::asSuccess()
            ->withMessage('Sub-categories fetched successfully.')
            ->withData([
                'sub_categories' => $subCategories,
            ])
            ->build();
    }

    /**
     * Get the specified subCategory's apartments.
     *
     * @param Request $request
     * @param mixed $subCategory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function apartments(Request $request, $subCategory)
    {
        $apartments = Apartment::query()
            ->whereHas('subCategory', function ($query) use ($subCategory) {
                $query->where('id', $subCategory)->orWhere('slug', $subCategory);
            })
            ->active()
            ->verified()
            ->noActiveRentals()
            ->with([
                'category:id,name,slug',
                'subCategory:id,name,slug',
                'apartmentDuration:id,period,duration_in_days',
            ])
            ->withCount([
                'rentals as stays',
            ])
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
}
