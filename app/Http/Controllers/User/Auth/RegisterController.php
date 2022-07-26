<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class RegisterController extends Controller
{
    /**
     * Create new user.
     *
     * @param RegisterRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->phone_number = $request->phone_number;
        $user->save();

        // Generate auth token for user.
        $token = $user->createToken('user')->plainTextToken;

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('User registered successfully.')
            ->withData([
                'user' => $user,
                'token' => $token,
            ])
            ->build();
    }
}
