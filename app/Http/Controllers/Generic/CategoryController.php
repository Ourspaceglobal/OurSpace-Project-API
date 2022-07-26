<?php

namespace App\Http\Controllers\Generic;

use App\Contracts\QueryBuilder\BasicInfoExtract;
use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Category;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $categories = QueryBuilder::for(Category::active())
            ->select(['id', 'name', 'slug', 'description'])
            ->allowedIncludes([
                AllowedInclude::custom('subCategories', new BasicInfoExtract([
                    'id',
                    'category_id',
                    'name',
                    'slug',
                ])),
            ])
            ->orderBy('name')
            ->get();

        return ResponseBuilder::asSuccess()
            ->withMessage('Categories fetched successfully.')
            ->withData([
                'categories' => $categories,
            ])
            ->build();
    }

    /**
     * Get the specified category's apartments.
     *
     * @param Request $request
     * @param mixed $category
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function apartments(Request $request, $category)
    {
        $apartments = Apartment::query()
            ->whereHas('category', function ($query) use ($category) {
                $query->where('id', $category)->orWhere('slug', $category);
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
