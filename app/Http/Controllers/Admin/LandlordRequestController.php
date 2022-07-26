<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\QueryBuilder\BasicUserInfoExtract;
use App\Enums\LandlordRequestStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeclineLandlordRequestRequest;
use App\Models\LandlordRequest;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class LandlordRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $landlordRequests = QueryBuilder::for(LandlordRequest::class)
            ->allowedFilters([
                'user_id',
                'status',
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'status',
                'updated_at',
                'created_at',
            ])
            ->allowedIncludes([
                AllowedInclude::custom('user', new BasicUserInfoExtract())
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Landlord requests fetched successfully.')
            ->withData([
                'landlord_requests' => $landlordRequests,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param mixed $landlordRequest
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(LandlordRequest $landlordRequest)
    {
        $landlordRequest->kycs = $landlordRequest->kycs();
        $landlordRequest = $landlordRequest->load([
            'user' => fn($query) => $query->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone_number',
            ])
        ]);

        return ResponseBuilder::asSuccess()
            ->withMessage('Landlord request fetched successfully.')
            ->withData([
                'landlord_request' => $landlordRequest,
            ])
            ->build();
    }

    /**
     * Approve the specified resource from storage.
     *
     * @param \App\Models\LandlordRequest $landlordRequest
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function approve(LandlordRequest $landlordRequest)
    {
        abort_if(
            $landlordRequest->status !== LandlordRequestStatuses::PENDING,
            403,
            "This request is already {$landlordRequest->status}"
        );

        $landlordRequest->status = LandlordRequestStatuses::APPROVED;
        $landlordRequest->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Landlord request approved successfully.')
            ->withData([
                'landlord_request' => $landlordRequest->unsetRelation('user'),
            ])
            ->build();
    }

    /**
     * Decline the specified resource from storage.
     *
     * @param DeclineLandlordRequestRequest $request
     * @param \App\Models\LandlordRequest $landlordRequest
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function decline(DeclineLandlordRequestRequest $request, LandlordRequest $landlordRequest)
    {
        abort_if(
            $landlordRequest->status !== LandlordRequestStatuses::PENDING,
            403,
            "This request is already {$landlordRequest->status}"
        );

        $landlordRequest->is_declined = true;
        $landlordRequest->declination_reason = $request->reason;
        $landlordRequest->status = LandlordRequestStatuses::DECLINED;
        $landlordRequest->declined_at = now();
        $landlordRequest->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Landlord request declined successfully.')
            ->withData([
                'landlord_request' => $landlordRequest->unsetRelation('user'),
            ])
            ->build();
    }
}
