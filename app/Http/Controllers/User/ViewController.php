<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ViewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $model = match ($request->model) {
            'apartments' => (new \App\Models\Apartment())->getMorphClass(),
            'posts' => (new \App\Models\Post())->getMorphClass(),
            default => null,
        };

        $views = $user->views()
            ->with([
                'model' => fn ($query) => $query
                    ->when(
                        $request->model === 'apartments',
                        fn ($query) => $query
                            ->verified()
                            ->active()
                            ->select([
                                'id',
                                'category_id',
                                'sub_category_id',
                                'apartment_duration_id',
                                'name',
                                'slug',
                                'price'
                            ])
                            ->with([
                                'category:id,name,slug',
                                'subCategory:id,name,slug',
                                'apartmentDuration:id,duration_in_days,period',
                                'galleries.media',
                            ])
                            ->withCount([
                                'rentals as stays',
                            ])
                    )
                    ->when(
                        $request->model === 'posts',
                        fn ($query) => $query
                            ->published()
                            ->with([
                                'admin:id,first_name,last_name',
                            ])
                            ->withCount([
                                'comments' => fn($query) => $query->approved()->whereNull('parent_id'),
                            ])
                    )
            ])
            ->when(
                (bool) $model,
                fn ($query) => $query->where('model_type', $model)
            )
            ->latest()
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Views fetched successfully.')
            ->withData([
                'views' => $views,
            ])
            ->build();
    }
}
