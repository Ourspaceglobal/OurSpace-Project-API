<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSystemApartmentKycRequest;
use App\Http\Requests\Admin\UpdateSystemApartmentKycRequest;
use App\Models\SystemApartmentKyc;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SystemApartmentKycController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $systemApartmentKycs = QueryBuilder::for(SystemApartmentKyc::class)
            ->allowedFilters([
                'name',
                AllowedFilter::trashed(),
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'name',
                'updated_at',
                'created_at',
            ])
            ->with([
                'datatype' => fn($query) => $query->select(['id', 'name', 'hint']),
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('System Apartment KYCs fetched successfully.')
            ->withData([
                'system_apartment_kycs' => $systemApartmentKycs,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSystemApartmentKycRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreSystemApartmentKycRequest $request)
    {
        $systemApartmentKyc = new SystemApartmentKyc();
        $systemApartmentKyc->datatype_id = $request->datatype_id;
        $systemApartmentKyc->name = $request->name;
        $systemApartmentKyc->description = $request->description;
        $systemApartmentKyc->is_required = $request->is_required;
        $systemApartmentKyc->save();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('System Apartment KYC created successfully.')
            ->withData([
                'system_apartment_kyc' => $systemApartmentKyc,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param mixed $systemApartmentKyc
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($systemApartmentKyc)
    {
        $systemApartmentKyc = QueryBuilder::for(SystemApartmentKyc::withTrashed()->where('id', $systemApartmentKyc))
            ->with([
                'datatype' => fn($query) => $query->select(['id', 'name', 'hint']),
            ])
            ->firstOrFail();

        return ResponseBuilder::asSuccess()
            ->withMessage('System Apartment KYC fetched successfully.')
            ->withData([
                'system_apartment_kyc' => $systemApartmentKyc,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSystemApartmentKycRequest $request
     * @param \App\Models\SystemApartmentKyc $systemApartmentKyc
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateSystemApartmentKycRequest $request, SystemApartmentKyc $systemApartmentKyc)
    {
        $systemApartmentKyc->datatype_id = $request->datatype_id;
        $systemApartmentKyc->name = $request->name;
        $systemApartmentKyc->description = $request->description;
        $systemApartmentKyc->is_required = $request->is_required;
        $systemApartmentKyc->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('System Apartment KYC updated successfully.')
            ->withData([
                'system_apartment_kyc' => $systemApartmentKyc,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\SystemApartmentKyc $systemApartmentKyc
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(SystemApartmentKyc $systemApartmentKyc)
    {
        $systemApartmentKyc->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param \App\Models\SystemApartmentKyc $systemApartmentKyc
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restore(SystemApartmentKyc $systemApartmentKyc)
    {
        $systemApartmentKyc->restore();

        return ResponseBuilder::asSuccess()
            ->withMessage('System Apartment KYC restored successfully.')
            ->withData([
                'system_apartment_kyc' => $systemApartmentKyc,
            ])
            ->build();
    }
}
