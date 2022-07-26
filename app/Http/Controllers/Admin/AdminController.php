<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignRoleRequest;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $admins = QueryBuilder::for(
            Admin::query()
                ->whereHas('roles', fn($query) => $query->where('name', '!=', 'SUPERADMIN'))
                ->orWhereDoesntHave('roles')
        )
            ->with([
                'roles' => fn($query) => $query->select('id', 'name'),
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'is_blocked',
                'updated_at',
                'created_at',
            ])
            ->allowedFilters([
                'is_blocked',
                AllowedFilter::scope('full_name'),
                AllowedFilter::trashed(),
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Admins fetched successfully.')
            ->withData([
                'admins' => $admins,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAdminRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreAdminRequest $request)
    {
        $admin = new Admin();
        $admin->first_name = $request->first_name;
        $admin->last_name = $request->last_name;
        $admin->email = $request->email;
        $admin->phone_number = $request->phone_number;
        $admin->password = bcrypt(random_bytes(4));
        $admin->save();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Admin created successfully.')
            ->withData([
                'admin' => $admin,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Admin $admin
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Admin $admin)
    {
        $admin->load([
            'roles',
        ]);

        return ResponseBuilder::asSuccess()
            ->withMessage('Admin fetched successfully.')
            ->withData([
                'admin' => $admin,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAdminRequest $request
     * @param \App\Models\Admin $admin
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateAdminRequest $request, Admin $admin)
    {
        abort_if($admin->hasRole('SUPERADMIN'), 403);

        $admin->first_name = $request->first_name;
        $admin->last_name = $request->last_name;
        $admin->email = $request->email;
        $admin->phone_number = $request->phone_number;
        $admin->save();

        if ($admin->wasChanged('email')) {
            $admin->tokens()->delete();
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('Admin updated successfully.')
            ->withData([
                'admin' => $admin,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Admin $admin
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Admin $admin)
    {
        abort_if($admin->hasRole('SUPERADMIN'), 403);

        $admin->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param \App\Models\Admin $admin
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restore(Admin $admin)
    {
        abort_if($admin->hasRole('SUPERADMIN'), 403);

        $admin->restore();

        return ResponseBuilder::asSuccess()
            ->withMessage('Admin restored successfully.')
            ->withData([
                'admin' => $admin,
            ])
            ->build();
    }

    /**
     * Toggle admin role.
     *
     * @param AssignRoleRequest $request
     * @param Admin $admin
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleRole(AssignRoleRequest $request, Admin $admin)
    {
        abort_if($admin->hasRole('SUPERADMIN'), 403);

        if ($admin->hasRole($request->role)) {
            $admin->removeRole($request->role);
        } else {
            $admin->assignRole($request->role);
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('Admin role updated successfully.')
            ->withData([
                'admin' => $admin,
            ])
            ->build();
    }

    /**
     * Toggle block status of the admin.
     *
     * @param Admin $admin
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleBlockStatus(Admin $admin)
    {
        abort_if($admin->hasRole('SUPERADMIN'), 403);

        $admin->is_blocked = !$admin->is_blocked;
        $admin->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Admin blocked status updated successfully.')
            ->withData([
                'admin' => $admin,
            ])
            ->build();
    }
}
