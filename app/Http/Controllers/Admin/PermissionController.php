<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $permissions = Permission::query()
            ->where('guard_name', 'api_admin')
            ->orderBy('group_name');

        if ($request->do_not_paginate) {
            $permissions = $permissions->get();
        } else {
            $permissions = $permissions->paginate($request->per_page);
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('Permissions fetched successfully.')
            ->withData([
                'permissions' => $permissions,
            ])
            ->build();
    }
}
