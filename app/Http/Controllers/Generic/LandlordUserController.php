<?php

namespace App\Http\Controllers\Generic;

use App\Enums\UserType;
use App\Events\User\ViewLogger;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class LandlordUserController extends Controller
{
    /**
     * Get a landlord's information.
     *
     * @param mixed $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request, $user)
    {
        $data = [];

        $landlord = User::query()
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone_number',
            ])
            ->with([
                'reviews' => fn($query) => $query->approved()->latest()->with([
                    'user:id,first_name,last_name,email,phone_number',
                ]),
            ])
            ->withCount([
                'views',
            ])
            ->where(fn($query) => $query->where('id', $user)->orWhere('finder', $user))
            ->where('type', UserType::LANDLORD)
            ->firstOrFail();

        abort_if(
            $landlord->is_blocked,
            Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS,
            'Landlord is currently suspended.'
        );

        event(new ViewLogger($landlord, $request->user()));

        $data['landlord'] = $landlord;

        if (str_contains($request->include, 'apartment')) {
            $apartments = $landlord->apartments()
                ->active()
                ->verified()
                ->with([
                    'category:id,name,slug',
                    'subCategory:id,name,slug',
                    'apartmentDuration:id,period,duration_in_days',
                ])
                ->withCount([
                    'rentals as stays',
                ])
                ->latest()
                ->paginate($request->per_page)
                ->withQueryString();

            $data['apartments'] = $apartments;
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('Landlord fetched successfully.')
            ->withData($data)
            ->build();
    }
}
