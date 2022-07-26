<?php

namespace App\Http\Controllers\User;

use App\Enums\MediaCollection;
use App\Events\User\ApartmentVerification;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreApartmentGalleryRequest;
use App\Http\Requests\User\UpdateApartmentGalleryRequest;
use App\Models\Apartment;
use App\Models\ApartmentGallery;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ApartmentGalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Apartment $apartment)
    {
        $galleries = $apartment->galleries()
            ->withCount([
                'media as images_count',
            ])
            ->get();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment galleries fetched successfully.')
            ->withData([
                'galleries' => $galleries,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreApartmentGalleryRequest $request
     * @param Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreApartmentGalleryRequest $request, Apartment $apartment)
    {
        $this->authorize('update', $apartment);

        DB::beginTransaction();

        $apartmentGallery = new ApartmentGallery();
        $apartmentGallery->title = $request->title;
        $apartmentGallery->apartment()->associate($apartment);
        $apartmentGallery->save();

        if ($request->images) {
            $apartmentGallery->addMultipleMediaFromRequest(['images'])
                ->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection(MediaCollection::GALLERY);
                });
        }

        event(new ApartmentVerification($apartment));

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Apartment gallery stored successfully.')
            ->withData([
                'apartment_gallery' => $apartmentGallery->unsetRelation('apartment'),
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Apartment $apartment
     * @param \App\Models\ApartmentGallery $gallery
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Apartment $apartment, ApartmentGallery $gallery)
    {
        $gallery->images = $gallery->images();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment gallery fetched successfully.')
            ->withData([
                'apartment_gallery' => $gallery,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateApartmentGalleryRequest $request
     * @param \App\Models\Apartment $apartment
     * @param \App\Models\ApartmentGallery $gallery
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        UpdateApartmentGalleryRequest $request,
        Apartment $apartment,
        ApartmentGallery $gallery
    ) {
        $this->authorize('update', $apartment);

        $gallery->title = $request->title;
        $gallery->save();

        event(new ApartmentVerification($apartment));

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment gallery updated successfully.')
            ->withData([
                'apartment_gallery' => $gallery,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Apartment $apartment
     * @param \App\Models\ApartmentGallery $gallery
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Apartment $apartment, ApartmentGallery $gallery)
    {
        $this->authorize('update', $apartment);

        DB::beginTransaction();

        $gallery->forceDelete();

        event(new ApartmentVerification($apartment));

        DB::commit();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
