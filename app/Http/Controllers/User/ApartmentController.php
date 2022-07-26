<?php

namespace App\Http\Controllers\User;

use App\Contracts\QueryBuilder\BasicInfoExtract;
use App\Enums\MediaCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreApartmentRequest;
use App\Http\Requests\User\UpdateApartmentRequest;
use App\Models\Apartment;
use App\Models\ApartmentGallery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class ApartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Apartment::class);

        $apartments = QueryBuilder::for($request->user()->apartments())
            ->allowedFilters([
                AllowedFilter::trashed(),
                'is_active',
                'is_verified',
                'is_featured',
                AllowedFilter::callback('date_added', function ($query, $date) {
                    $formattedDate = explode('/', $date);

                    throw_if(
                        count($formattedDate) <> 2,
                        \App\Exceptions\InvalidFormatException::class,
                        'Incorrect format for date filter. Expects month/year.'
                    );

                    $month = head($formattedDate);
                    $year = last($formattedDate);

                    $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
                })
            ])
            ->allowedIncludes([
                AllowedInclude::custom('category', new BasicInfoExtract([
                    'id',
                    'name',
                    'description',
                ])),
                AllowedInclude::custom('subCategory', new BasicInfoExtract([
                    'id',
                    'name',
                    'description',
                ])),
                AllowedInclude::custom('apartmentDuration', new BasicInfoExtract([
                    'id',
                    'period',
                    'duration_in_days',
                ])),
                AllowedInclude::custom('user', new BasicInfoExtract([
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone_number',
                ])),
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'name',
                'is_active',
                'is_verified',
                'is_featured',
                'updated_at',
                'created_at',
            ])
            ->withCount([
                'rentals as stays',
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartments fetched successfully.')
            ->withData([
                'apartments' => $apartments,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreApartmentRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreApartmentRequest $request)
    {
        $this->authorize('create', Apartment::class);

        DB::beginTransaction();

        $apartment = new Apartment();
        $apartment->name = $request->name;
        $apartment->description = $request->description;
        $apartment->category_id = $request->category_id;
        $apartment->sub_category_id = $request->sub_category_id;
        $apartment->user()->associate($request->user());
        $apartment->price = number_format((float) $request->price, 2, '.', '');
        $apartment->apartment_duration_id = $request->apartment_duration_id;
        $apartment->save();

        if ($request->featured_image) {
            $apartment->addMediaFromRequest('featured_image')->toMediaCollection(MediaCollection::FEATUREDIMAGE);
        }

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Apartment stored successfully.')
            ->withData([
                'apartment' => $apartment->unsetRelation('user'),
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param mixed $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request, $apartment)
    {
        $apartment = QueryBuilder::for(
            $request->user()->apartments()->withTrashed()->where('id', $apartment)->orWhere('slug', $apartment)
        )
            ->allowedIncludes([
                AllowedInclude::custom('category', new BasicInfoExtract([
                    'id',
                    'name',
                    'description',
                ])),
                AllowedInclude::custom('subCategory', new BasicInfoExtract([
                    'id',
                    'name',
                    'description',
                ])),
                AllowedInclude::custom('apartmentDuration', new BasicInfoExtract([
                    'id',
                    'period',
                    'duration_in_days',
                ])),
                AllowedInclude::custom('user', new BasicInfoExtract([
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone_number',
                ])),
                'location',
            ])
            ->withCount([
                'rentals as stays',
            ])
            ->firstOrFail();

        $this->authorize('view', $apartment);

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment fetched successfully.')
            ->withData([
                'apartment' => $apartment,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateApartmentRequest $request
     * @param \App\Models\Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateApartmentRequest $request, Apartment $apartment)
    {
        $this->authorize('update', $apartment);

        DB::beginTransaction();

        $apartment->name = $request->name;
        $apartment->description = $request->description;
        $apartment->category_id = $request->category_id;
        $apartment->sub_category_id = $request->sub_category_id;
        $apartment->price = number_format((float) $request->price, 2, '.', '');
        $apartment->apartment_duration_id = $request->apartment_duration_id;
        $apartment->save();

        if ($request->featured_image) {
            $apartment->addMediaFromRequest('featured_image')->toMediaCollection(MediaCollection::FEATUREDIMAGE);
        }

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment updated successfully.')
            ->withData([
                'apartment' => $apartment->unsetRelation('user'),
            ])
            ->build();
    }

    /**
     * Toggle the active status of the specified resource in storage.
     *
     * @param \App\Models\Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleActiveStatus(Apartment $apartment)
    {
        $this->authorize('update', $apartment);

        $apartment->is_active = !$apartment->is_active;
        $apartment->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment active status updated successfully.')
            ->withData([
                'apartment' => $apartment,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Apartment $apartment)
    {
        $this->authorize('delete', $apartment);

        $apartment->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param \App\Models\Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restore(Apartment $apartment)
    {
        $this->authorize('restore', $apartment);

        $apartment->restore();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment restored successfully.')
            ->withData([
                'apartment' => $apartment,
            ])
            ->build();
    }

    /**
     * Duplicate the specified resource.
     *
     * @param Apartment $apartment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function duplicate(Apartment $apartment)
    {
        $this->authorize('update', $apartment);

        DB::beginTransaction();

        // Replicate the apartment
        $newApartment = $apartment->replicate();
        $newApartment->name = "{$newApartment->name} (copy)";
        $newApartment->is_verified = false;
        $newApartment->verified_at = null;
        $newApartment->is_featured = false;
        $newApartment->featured_at = null;
        if ($apartment->featured_image) {
            $newApartment->addMediaFromUrl($apartment->featured_image)
                ->toMediaCollection(MediaCollection::FEATUREDIMAGE);
        }
        $newApartment->save();

        // Location
        if ($apartment->location) {
            $newApartmentLocation = $apartment->location->replicate();
            $newApartmentLocation->apartment_id = $newApartment->id;
            $newApartmentLocation->save();
        }

        // Contact
        if ($apartment->contact) {
            $newApartmentContact = $apartment->contact->replicate();
            $newApartmentContact->apartment_id = $newApartment->id;
            $newApartmentContact->save();
        }

        // Custom KYCs
        foreach ($apartment->customApartmentKycs as $customApartmentKyc) {
            $newApartment->customApartmentKycs()->attach($customApartmentKyc->id);
        }

        // Amenities
        foreach ($apartment->amenities as $amenity) {
            $newApartment->amenities()->attach($amenity->id, [
                'total_number' => $amenity->pivot->total_number,
            ]);
        }

        // Galleries
        foreach ($apartment->galleries()->with('media')->get() as $gallery) {
            $newApartmentGallery = new ApartmentGallery();
            $newApartmentGallery->title = $gallery->title;
            $newApartmentGallery->apartment()->associate($newApartment);
            $newApartmentGallery->save();

            if ($images = $gallery->images()) {
                foreach ($images as $image) {
                    $newApartmentGallery->addMediaFromUrl($image->original_url)
                        ->toMediaCollection(MediaCollection::GALLERY);
                }
            }
        }

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment duplicated successfully.')
            ->withData([
                'apartment' => $newApartment,
            ])
            ->build();
    }
}
