<?php

namespace App\Http\Controllers\Generic;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

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
        $cities = City::query()
            ->select(['id', 'name', 'state_id'])
            ->orderBy('name')
            ->when($request->state_id, fn ($query, $state_id) => $query->where('state_id', $state_id));

        if ($request->do_not_paginate) {
            $cities = $cities->get();
        } else {
            $cities = $cities->paginate($request->per_page)->withQueryString();
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('Cities fetched successfully.')
            ->withData([
                'cities' => $cities,
            ])
            ->build();
    }
}
