<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MediaCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAmenityRequest;
use App\Http\Requests\Admin\UpdateAmenityRequest;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AmenityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $amenities = QueryBuilder::for(Amenity::class)
            ->allowedFilters([
                'name',
                'is_primary',
                'is_active',
                AllowedFilter::trashed(),
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'name',
                'is_primary',
                'is_active',
                'updated_at',
                'created_at',
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Amenities fetched successfully.')
            ->withData([
                'amenities' => $amenities,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAmenityRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreAmenityRequest $request)
    {
        DB::beginTransaction();

        $amenity = new Amenity();
        $amenity->name = $request->name;
        $amenity->is_primary = $request->is_primary;
        $amenity->save();

        $amenity->addMediaFromRequest('icon')->toMediaCollection(MediaCollection::ICON);

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Amenity created successfully.')
            ->withData([
                'amenity' => $amenity,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param mixed $amenity
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($amenity)
    {
        $amenity = QueryBuilder::for(Amenity::withTrashed()->where('id', $amenity))
            ->allowedIncludes([
                // AllowedInclude::count('apartments'),
            ])
            ->firstOrFail();

        return ResponseBuilder::asSuccess()
            ->withMessage('Amenity fetched successfully.')
            ->withData([
                'amenity' => $amenity,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAmenityRequest $request
     * @param \App\Models\Amenity $amenity
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateAmenityRequest $request, Amenity $amenity)
    {
        DB::beginTransaction();

        $amenity->name = $request->name;
        $amenity->is_primary = $request->is_primary;
        $amenity->save();

        if ($request->icon) {
            $amenity->addMediaFromRequest('icon')->toMediaCollection(MediaCollection::ICON);
        }

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Amenity updated successfully.')
            ->withData([
                'amenity' => $amenity,
            ])
            ->build();
    }

    /**
     * Toggle the specified resource's is_active status.
     *
     * @param Amenity $amenity
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleActiveStatus(Amenity $amenity)
    {
        $amenity->is_active = !$amenity->is_active;
        $amenity->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Amenity active status updated successfully.')
            ->withData([
                'amenity' => $amenity,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Amenity $amenity
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Amenity $amenity)
    {
        $amenity->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param \App\Models\Amenity $amenity
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restore(Amenity $amenity)
    {
        $amenity->restore();

        return ResponseBuilder::asSuccess()
            ->withMessage('Amenity restored successfully.')
            ->withData([
                'amenity' => $amenity,
            ])
            ->build();
    }
}
