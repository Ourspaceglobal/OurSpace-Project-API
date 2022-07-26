<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSystemDataRequest;
use App\Models\SystemData;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class SystemDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $systemData = SystemData::query()
            ->with([
                'datatype' => fn($query) => $query->select(['id', 'name', 'hint']),
            ])
            ->latest('updated_at')
            ->get();

        return ResponseBuilder::asSuccess()
            ->withMessage('System data fetched successfully.')
            ->withData([
                'system_data' => $systemData,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\SystemData $systemDatum
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(SystemData $systemDatum)
    {
        $systemDatum = $systemDatum->load([
            'datatype' => fn($query) => $query->select(['id', 'name', 'hint'])
        ]);

        return ResponseBuilder::asSuccess()
            ->withMessage('System data fetched successfully.')
            ->withData([
                'system_data' => $systemDatum,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSystemDataRequest $request
     * @param \App\Models\SystemData $systemDatum
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateSystemDataRequest $request, SystemData $systemDatum)
    {
        $systemDatum->content = $request->content;
        $systemDatum->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('System data updated successfully.')
            ->withData([
                'system_data' => $systemDatum->unsetRelation('datatype'),
            ])
            ->build();
    }
}
