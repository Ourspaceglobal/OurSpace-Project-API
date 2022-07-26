<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStateRequest;
use App\Http\Requests\Admin\UpdateStateRequest;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $states = QueryBuilder::for(State::class)
            ->allowedIncludes([
                'cities',
                'localGovernments',
            ])
            ->allowedFilters([
                'name',
                AllowedFilter::trashed(),
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'name',
                'updated_at',
                'created_at',
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('States fetched successfully.')
            ->withData([
                'states' => $states,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreStateRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreStateRequest $request)
    {
        $state = new State();
        $state->name = $request->name;
        $state->save();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('State created successfully.')
            ->withData([
                'state' => $state,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param mixed $state
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($state)
    {
        $state = QueryBuilder::for(State::withTrashed()->where('id', $state))
            ->allowedIncludes([
                'cities',
                'localGovernments',
            ])
            ->firstOrFail();

        return ResponseBuilder::asSuccess()
            ->withMessage('State fetched successfully.')
            ->withData([
                'state' => $state,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateStateRequest $request
     * @param \App\Models\State $state
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateStateRequest $request, State $state)
    {
        $state->name = $request->name;
        $state->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('State updated successfully.')
            ->withData([
                'state' => $state,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\State $state
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(State $state)
    {
        $state->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param \App\Models\State $state
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restore(State $state)
    {
        $state->restore();

        return ResponseBuilder::asSuccess()
            ->withMessage('State restored successfully.')
            ->withData([
                'state' => $state,
            ])
            ->build();
    }
}
