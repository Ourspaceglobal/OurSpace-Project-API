<?php

namespace App\Http\Controllers\User;

use App\Enums\LandlordRequestStatuses;
use App\Enums\MediaCollection;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreLandlordRequestRequest;
use App\Models\LandlordRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
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
        $user = $request->user();

        $landlordRequests = QueryBuilder::for($user->landlordRequests())
            ->allowedFilters([
                'status',
            ])
            ->defaultSort('-updated_at')
            ->paginate()
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Landlord requests fetched successfully.')
            ->withData([
                'landlord_requests' => $landlordRequests,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreLandlordRequestRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreLandlordRequestRequest $request)
    {
        $user = $request->user();

        abort_if($user->type === UserType::LANDLORD, 403, 'You are already a landlord.');
        abort_if($user->landlordRequests()->pending()->exists(), 403, 'You still have an open request.');

        DB::beginTransaction();

        $landlordRequest = new LandlordRequest();
        $landlordRequest->user_id = $user->id;
        $landlordRequest->note = $request->note;
        $landlordRequest->national_identity_number = $request->national_identity_number;
        $landlordRequest->save();

        $landlordRequest->addMultipleMediaFromRequest(['kycs'])
            ->each(function ($fileAdder) {
                $fileAdder->toMediaCollection(MediaCollection::KYC);
            });

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Landlord request was submitted successfully.')
            ->withData([
                'landlord_request' => $landlordRequest->unsetRelation('user'),
            ])
            ->build();
    }

    /**
     * Display the specified resource from storage.
     *
     * @param \App\Models\LandlordRequest $landlordRequest
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(LandlordRequest $landlordRequest)
    {
        $this->authorize('view', $landlordRequest);

        $landlordRequest->kycs = $landlordRequest->kycs();

        return ResponseBuilder::asSuccess()
            ->withMessage('Landlord request fetched successfully.')
            ->withData([
                'landlord_request' => $landlordRequest,
            ])
            ->build();
    }

    /**
     * Close the specified resource from storage.
     *
     * @param \App\Models\LandlordRequest $landlordRequest
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function close(LandlordRequest $landlordRequest)
    {
        $this->authorize('update', $landlordRequest);

        abort_if(
            in_array($landlordRequest->status, [
                LandlordRequestStatuses::APPROVED,
                LandlordRequestStatuses::DECLINED,
                LandlordRequestStatuses::CLOSED
            ]),
            403,
            "This request is already {$landlordRequest->status}"
        );

        $landlordRequest->status = LandlordRequestStatuses::CLOSED;
        $landlordRequest->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Landlord request closed successfully.')
            ->withData([
                'landlord_request' => $landlordRequest->unsetRelation('user'),
            ])
            ->build();
    }
}
