<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCityRequest;
use App\Http\Requests\Admin\UpdateCityRequest;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $cities = QueryBuilder::for(City::class)
            ->allowedIncludes([
                'state',
                'localGovernments',
            ])
            ->allowedFilters([
                'state_id',
                'name',
                AllowedFilter::trashed(),
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'name',
                'updated_at',
                'created_at',
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Cities fetched successfully.')
            ->withData([
                'cities' => $cities,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCityRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreCityRequest $request)
    {
        $city = new City();
        $city->state_id = $request->state_id;
        $city->name = $request->name;
        $city->save();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('City created successfully.')
            ->withData([
                'city' => $city,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param mixed $city
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($city)
    {
        $city = QueryBuilder::for(City::withTrashed()->where('id', $city))
            ->allowedIncludes([
                'state',
                'localGovernments',
            ])
            ->firstOrFail();

        return ResponseBuilder::asSuccess()
            ->withMessage('City fetched successfully.')
            ->withData([
                'city' => $city,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCityRequest $request
     * @param \App\Models\City $city
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateCityRequest $request, City $city)
    {
        $city->state_id = $request->state_id;
        $city->name = $request->name;
        $city->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('City updated successfully.')
            ->withData([
                'city' => $city,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\City $city
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(City $city)
    {
        $city->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param \App\Models\City $city
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restore(City $city)
    {
        $city->restore();

        return ResponseBuilder::asSuccess()
            ->withMessage('City restored successfully.')
            ->withData([
                'city' => $city,
            ])
            ->build();
    }
}
