<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MediaCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Account\UpdateAdminAvatarRequest;
use App\Http\Requests\Admin\Account\UpdateAdminPasswordRequest;
use App\Http\Requests\Admin\Account\UpdateAdminRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class AccountController extends Controller
{
    /**
     * Get admin data.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        return ResponseBuilder::asSuccess()
            ->withMessage('Admin profile fetched successfully.')
            ->withData([
                'admin' => $request->user(),
            ])
            ->build();
    }

    /**
     * Update user data.
     *
     * @param UpdateAdminRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateAdminRequest $request)
    {
        $admin = $request->user();
        $admin->first_name = $request->first_name;
        $admin->last_name = $request->last_name;
        $admin->email = $request->email;
        $admin->phone_number = $request->phone_number;
        $admin->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Admin profile updated successfully.')
            ->withData([
                'admin' => $admin,
            ])
            ->build();
    }

    /**
     * Update user avatar.
     *
     * @param UpdateAdminAvatarRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAvatar(UpdateAdminAvatarRequest $request)
    {
        $admin = $request->user();
        $admin->addMediaFromRequest('avatar')->toMediaCollection(MediaCollection::AVATAR);

        return  ResponseBuilder::asSuccess()
            ->withMessage('Admin avatar updated successfully.')
            ->withData([
                'admin' => $admin,
            ])
            ->build();
    }

    /**
     * Update user password.
     *
     * @param UpdateAdminPasswordRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updatePassword(UpdateAdminPasswordRequest $request)
    {
        $admin = $request->user();
        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return  ResponseBuilder::asSuccess()
            ->withMessage('Admin password updated successfully.')
            ->build();
    }

    /**
     * Update admin 2FA status.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateTwoFa(Request $request)
    {
        $admin = $request->user();
        $admin->is_2fa_active = !$admin->is_2fa_active;
        $admin->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Admin 2FA status updated successfully.')
            ->withData([
                'admin' => $admin,
            ])
            ->build();
    }

    /**
     * Get the admin permissions.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPermissions(Request $request)
    {
        $permissions = $request->user()->getAllPermissions();

        return ResponseBuilder::asSuccess()
            ->withMessage('Admin permissions fetched successfully.')
            ->withData([
                'permissions' => $permissions,
            ])
            ->build();
    }
}
