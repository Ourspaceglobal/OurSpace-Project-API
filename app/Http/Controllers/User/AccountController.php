<?php

namespace App\Http\Controllers\User;

use App\Enums\MediaCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Account\UpdateUserAvatarRequest;
use App\Http\Requests\User\Account\UpdateUserPasswordRequest;
use App\Http\Requests\User\Account\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class AccountController extends Controller
{
    /**
     * Get user data.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->type === 'landlord') {
            $user->load([
                'landlordRequests' => fn ($query) => $query
                    ->select(['id', 'user_id', 'national_identity_number', 'status'])
                    ->latest()
                    ->limit(1),
            ]);

            $user->landlord_requests = collect($user->landlordRequests)->map(fn ($landlordRequest) => [
                $landlordRequest->kycs = $landlordRequest->kycs(),
            ]);
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('User profile fetched successfully.')
            ->withData([
                'user' => $user,
            ])
            ->build();
    }

    /**
     * Update user data.
     *
     * @param UpdateUserRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateUserRequest $request)
    {
        $user = $request->user();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->country = $request->country;
        $user->state = $request->state;
        $user->gender = $request->gender;
        $user->date_of_birth = $request->date_of_birth;
        $user->home_address = $request->home_address;
        $user->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('User profile updated successfully.')
            ->withData([
                'user' => $user,
            ])
            ->build();
    }

    /**
     * Update user avatar.
     *
     * @param UpdateUserAvatarRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAvatar(UpdateUserAvatarRequest $request)
    {
        $user = $request->user();
        $user->addMediaFromRequest('avatar')->toMediaCollection(MediaCollection::AVATAR);

        return  ResponseBuilder::asSuccess()
            ->withMessage('User avatar updated successfully.')
            ->withData([
                'user' => $user,
            ])
            ->build();
    }

    /**
     * Update user password.
     *
     * @param UpdateUserPasswordRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updatePassword(UpdateUserPasswordRequest $request)
    {
        $user = $request->user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('User password updated successfully.')
            ->build();
    }

    /**
     * Update user 2FA status.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateTwoFa(Request $request)
    {
        $user = $request->user();
        $user->is_2fa_active = !$user->is_2fa_active;
        $user->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('User 2FA status updated successfully.')
            ->withData([
                'user' => $user,
            ])
            ->build();
    }
}
