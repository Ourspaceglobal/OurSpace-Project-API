<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreApartmentContactRequest;
use App\Models\Apartment;
use App\Models\ApartmentContact;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ApartmentContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Apartment $apartment)
    {
        $contact = $apartment->contact;

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment contact fetched successfully.')
            ->withData([
                'contact' => $contact,
            ])
            ->build();
    }

    /**
     * Store/Update the specified resource.
     *
     * @param StoreApartmentContactRequest $request
     * @param Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreApartmentContactRequest $request, Apartment $apartment)
    {
        $this->authorize('update', $apartment);

        $user = $request->user();

        $apartmentContact = ApartmentContact::query()->whereBelongsTo($apartment)->firstOrNew([]);
        $apartmentContact->apartment_id = $apartment->id;
        $apartmentContact->name = $request->name ?? $user->full_name;
        $apartmentContact->email = $request->email ?? $user->email;
        $apartmentContact->phone_number_1 = $request->phone_number_1 ?? $user->phone_number;
        $apartmentContact->phone_number_2 = $request->phone_number_2;
        $apartmentContact->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment contact stored successfully.')
            ->withData([
                'contact' => $apartmentContact,
            ])
            ->build();
    }
}
