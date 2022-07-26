<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::exact('finder'),
                'type',
                'email',
                'is_blocked',
                AllowedFilter::trashed(),
                AllowedFilter::scope('full_name'),
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'wallet_balance',
                'temp_wallet_balance',
                'is_blocked',
                'updated_at',
                'created_at',
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Users fetched successfully.')
            ->withData([
                'users' => $users,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param mixed $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($user)
    {
        $user = QueryBuilder::for(User::where('id', $user)->orWhere('email', $user)->orWhere('finder', $user))
            ->allowedFilters([
                'finder',
                'email',
                AllowedFilter::trashed(),
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'updated_at',
                'created_at',
            ])
            ->firstOrFail();

        return ResponseBuilder::asSuccess()
            ->withMessage('User fetched successfully.')
            ->withData([
                'user' => $user,
            ])
            ->build();
    }

    /**
     * Toggle block status of the user.
     *
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleBlockStatus(User $user)
    {
        $user->is_blocked = !$user->is_blocked;
        $user->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('User blocked status updated successfully.')
            ->withData([
                'user' => $user,
            ])
            ->build();
    }
}
