<?php

namespace App\Http\Controllers\Generic;

use App\Http\Controllers\Controller;
use App\Models\Datatype;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class DatatypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $datatypes = Datatype::select(['id', 'name', 'hint', 'developer_hint'])->orderBy('name')->get();

        return ResponseBuilder::asSuccess()
            ->withMessage('Datatypes fetched successfully.')
            ->withData([
                'datatypes' => $datatypes,
            ])
            ->build();
    }
}
