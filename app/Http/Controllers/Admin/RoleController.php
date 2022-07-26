<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $roles = QueryBuilder::for(
            Role::query()->where('guard_name', 'api_admin')->where('name', '!=', 'SUPERADMIN')
        )
            ->allowedIncludes([
                'permissions',
                AllowedInclude::count('usersCount'),
            ]);

        if ($request->do_not_paginate) {
            $roles = $roles->get();
        } else {
            $roles = $roles->paginate($request->per_page);
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('Roles fetched successfully.')
            ->withData([
                'roles' => $roles,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRoleRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreRoleRequest $request)
    {
        DB::beginTransaction();

        $role = new Role();
        $role->name = $request->name;
        $role->guard_name = 'api_admin';
        $role->save();

        $role->syncPermissions(Permission::query()->whereIn('id', $request->permissions)->get());

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Role created successfully.')
            ->withData([
                'role' => $role,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Role $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Role $role)
    {
        $role->load([
            'permissions',
        ]);

        return ResponseBuilder::asSuccess()
            ->withMessage('Role fetched successfully.')
            ->withData([
                'role' => $role,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRoleRequest $request
     * @param \App\Models\Role $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        DB::beginTransaction();

        $role->name = $request->name;
        $role->guard_name = 'api_admin';
        $role->save();

        if ($request->permissions) {
            $role->syncPermissions(Permission::query()->whereIn('id', $request->permissions)->get());
        }

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Role updated successfully.')
            ->withData([
                'role' => $role,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Role $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
