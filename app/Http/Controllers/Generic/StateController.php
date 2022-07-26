<?php

namespace App\Http\Controllers\Generic;

use App\Http\Controllers\Controller;
use App\Models\State;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $states = State::query()->select(['id', 'name'])->orderBy('name');

        if ($request->do_not_paginate) {
            $states = $states->get();
        } else {
            $states = $states->paginate($request->per_page)->withQueryString();
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('States fetched successfully.')
            ->withData([
                'states' => $states,
            ])
            ->build();
    }
}
