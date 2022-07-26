<?php

namespace App\Http\Controllers\Generic;

use App\Http\Controllers\Controller;
use App\Models\LocalGovernment;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class LocalGovernmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $localGovernments = LocalGovernment::query()
            ->select(['id', 'name', 'city_id'])
            ->orderBy('name')
            ->when($request->city_id, fn ($query, $city_id) => $query->where('city_id', $city_id));

        if ($request->do_not_paginate) {
            $localGovernments = $localGovernments->get();
        } else {
            $localGovernments = $localGovernments->paginate($request->per_page)->withQueryString();
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('Local governments fetched successfully.')
            ->withData([
                'local_governments' => $localGovernments,
            ])
            ->build();
    }
}
