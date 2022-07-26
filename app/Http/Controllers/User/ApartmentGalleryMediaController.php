<?php

namespace App\Http\Controllers\User;

use App\Enums\MediaCollection;
use App\Events\User\ApartmentVerification;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreApartmentGalleryMediaRequest;
use App\Models\Apartment;
use App\Models\ApartmentGallery;
use App\Models\Media;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ApartmentGalleryMediaController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param StoreApartmentGalleryMediaRequest $request
     * @param \App\Models\Apartment $apartment
     * @param \App\Models\ApartmentGallery $gallery
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreApartmentGalleryMediaRequest $request, Apartment $apartment, ApartmentGallery $gallery)
    {
        $this->authorize('update', $apartment);

        DB::beginTransaction();

        $gallery->addMultipleMediaFromRequest(['images'])
            ->each(function ($fileAdder) {
                $fileAdder->toMediaCollection(MediaCollection::GALLERY);
            });

        event(new ApartmentVerification($apartment));

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment gallery image(s) added successfully.')
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
     * @param \App\Models\Media $media
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Apartment $apartment, ApartmentGallery $gallery, Media $media)
    {
        $this->authorize('update', $apartment);

        DB::beginTransaction();

        $media->forceDelete();

        event(new ApartmentVerification($apartment));

        DB::commit();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
