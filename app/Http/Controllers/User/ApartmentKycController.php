<?php

namespace App\Http\Controllers\User;

use App\Enums\MediaCollection;
use App\Enums\UserType;
use App\Events\User\ApartmentVerification;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreCustomApartmentKycRequest;
use App\Http\Requests\User\StoreUserApartmentKycRequest;
use App\Models\Apartment;
use App\Models\ApartmentRental;
use App\Models\SystemApartmentKyc;
use App\Models\UserApartmentKyc;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ApartmentKycController extends Controller
{
    /**
     * Display a listing of the specified resource.
     *
     * @param Request $request
     * @param Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, Apartment $apartment)
    {
        $user = $request->user();

        $apartmentKycs = [];
        $systemApartmentKycs = [];
        $customApartmentKycs = [];

        if ($user->type === UserType::TENANT) {
            if ((bool) $request->show_for_selection) {
                $systemApartmentKycs = SystemApartmentKyc::required()->with('datatype:id,name')->get();

                $customApartmentKycs = $apartment->customApartmentKycs()->with('datatype:id,name')->get();
            } else {
                $apartmentKycs = $user->apartmentKycs()->whereBelongsTo($apartment)
                    ->with([
                        'systemApartmentKyc.datatype:id,name',
                    ])
                    ->get()
                    ->each(fn($apartmentKyc) =>
                        $apartmentKyc->entry = $apartmentKyc->entry
                        ?? $apartmentKyc->getMedia(MediaCollection::KYC)->first()?->original_url);
            }
        } else {
            if ((bool) $request->show_for_selection) {
                $systemApartmentKycs = SystemApartmentKyc::required(false)->with('datatype:id,name')->get();
            } else {
                $systemApartmentKycs = SystemApartmentKyc::required()->with('datatype:id,name')->get();

                $customApartmentKycs = $apartment->customApartmentKycs()->with('datatype:id,name')->get();
            }
        }

        $data = [];

        if (count($apartmentKycs)) {
            $data['apartment_kycs'] = $apartmentKycs;
        }

        if (count($systemApartmentKycs)) {
            $data['system_apartment_kycs'] = $systemApartmentKycs;
        }

        if (count($customApartmentKycs)) {
            $data['custom_apartment_kycs'] = $customApartmentKycs;
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment KYCs fetched successfully.')
            ->withData($data)
            ->build();
    }

    /**
     * Store custom apartment KYCs.
     *
     * @param StoreCustomApartmentKycRequest $request
     * @param Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreCustomApartmentKycRequest $request, Apartment $apartment)
    {
        $this->authorize('view', $apartment);

        DB::beginTransaction();

        $apartment->customApartmentKycs()->sync($request->system_apartment_kyc_ids);

        event(new ApartmentVerification($apartment));

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Apartment KYCs added successfully.')
            ->build();
    }

    /**
     * Submit the user KYCs for the specified resource.
     *
     * @param StoreUserApartmentKycRequest $request
     * @param Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enroll(StoreUserApartmentKycRequest $request, Apartment $apartment)
    {
        $this->authorize('create', [ApartmentRental::class, $apartment]);

        $user = $request->user();

        DB::beginTransaction();

        $user->apartmentKycs()->whereBelongsTo($apartment)->delete();

        foreach ($request->system_apartment_kycs as $id => $value) {
            $userApartmentKyc = new UserApartmentKyc();
            $userApartmentKyc->user()->associate($user);
            $userApartmentKyc->apartment()->associate($apartment);
            $userApartmentKyc->system_apartment_kyc_id = $id;

            if (is_file($request->system_apartment_kycs[$id])) {
                $userApartmentKyc->addMedia($request->system_apartment_kycs[$id])
                    ->toMediaCollection(MediaCollection::KYC);
            } else {
                $userApartmentKyc->entry = $value;
            }

            $userApartmentKyc->save();
        }

        foreach ($request->custom_apartment_kycs as $id => $value) {
            $userApartmentKyc = new UserApartmentKyc();
            $userApartmentKyc->user()->associate($user);
            $userApartmentKyc->apartment()->associate($apartment);
            $userApartmentKyc->system_apartment_kyc_id = $id;

            if (is_file($request->custom_apartment_kycs[$id])) {
                $userApartmentKyc->addMedia($request->custom_apartment_kycs[$id])
                    ->toMediaCollection(MediaCollection::KYC);
            } else {
                $userApartmentKyc->entry = $value;
            }

            $userApartmentKyc->save();
        }

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('User apartment KYCs stored successfully.')
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Apartment $apartment
     * @param SystemApartmentKyc $systemApartmentKyc
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Apartment $apartment, SystemApartmentKyc $systemApartmentKyc)
    {
        $this->authorize('delete', $apartment);

        DB::beginTransaction();

        $apartment->customApartmentKycs()->detach($systemApartmentKyc->id);

        event(new ApartmentVerification($apartment));

        DB::commit();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
