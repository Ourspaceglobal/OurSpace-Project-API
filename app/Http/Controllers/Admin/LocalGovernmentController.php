<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLocalGovernmentRequest;
use App\Http\Requests\Admin\UpdateLocalGovernmentRequest;
use App\Models\LocalGovernment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

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
        $localGovernments = QueryBuilder::for(LocalGovernment::class)
            ->allowedIncludes([
                'city.state',
            ])
            ->allowedFilters([
                'city_id',
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
            ->withMessage('Local governments fetched successfully.')
            ->withData([
                'local_governments' => $localGovernments,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreLocalGovernmentRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreLocalGovernmentRequest $request)
    {
        $localGovernment = new LocalGovernment();
        $localGovernment->city_id = $request->city_id;
        $localGovernment->name = $request->name;
        $localGovernment->save();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Local government created successfully.')
            ->withData([
                'local_government' => $localGovernment,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param mixed $localGovernment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($localGovernment)
    {
        $localGovernment = QueryBuilder::for(LocalGovernment::withTrashed()->where('id', $localGovernment))
            ->allowedIncludes([
                'city',
            ])
            ->firstOrFail();

        return ResponseBuilder::asSuccess()
            ->withMessage('Local government fetched successfully.')
            ->withData([
                'local_government' => $localGovernment,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateLocalGovernmentRequest $request
     * @param \App\Models\LocalGovernment $localGovernment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateLocalGovernmentRequest $request, LocalGovernment $localGovernment)
    {
        $localGovernment->city_id = $request->city_id;
        $localGovernment->name = $request->name;
        $localGovernment->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Local government updated successfully.')
            ->withData([
                'local_government' => $localGovernment,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\LocalGovernment $localGovernment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(LocalGovernment $localGovernment)
    {
        $localGovernment->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param \App\Models\LocalGovernment $localGovernment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restore(LocalGovernment $localGovernment)
    {
        $localGovernment->restore();

        return ResponseBuilder::asSuccess()
            ->withMessage('Local government restored successfully.')
            ->withData([
                'local_government' => $localGovernment,
            ])
            ->build();
    }
}
