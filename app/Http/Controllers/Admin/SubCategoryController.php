<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MediaCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubCategoryRequest;
use App\Http\Requests\Admin\UpdateSubCategoryRequest;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $subCategories = QueryBuilder::for(SubCategory::class)
            ->allowedIncludes([
                'category',
                AllowedInclude::count('apartmentsCount', 'apartments'),
            ])
            ->allowedFilters([
                'name',
                'is_active',
                'category_id',
                AllowedFilter::trashed(),
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'name',
                'is_active',
                'updated_at',
                'created_at',
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Sub-categories fetched successfully.')
            ->withData([
                'sub_categories' => $subCategories,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSubCategoryRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreSubCategoryRequest $request)
    {
        $subCategory = new SubCategory();
        $subCategory->category_id = $request->category_id;
        $subCategory->name = $request->name;
        $subCategory->description = $request->description;
        $subCategory->save();

        if ($request->icon) {
            $subCategory->addMediaFromRequest('icon')->toMediaCollection(MediaCollection::ICON);
        }

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Sub-category created successfully.')
            ->withData([
                'sub_category' => $subCategory,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param mixed $subCategory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($subCategory)
    {
        $subCategory = QueryBuilder::for(SubCategory::withTrashed()->where('id', $subCategory))
            ->allowedIncludes([
                'category',
                AllowedInclude::count('apartmentsCount', 'apartments'),
            ])
            ->firstOrFail();

        return ResponseBuilder::asSuccess()
            ->withMessage('Sub-category fetched successfully.')
            ->withData([
                'sub_category' => $subCategory,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSubCategoryRequest $request
     * @param \App\Models\SubCategory $subCategory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateSubCategoryRequest $request, SubCategory $subCategory)
    {
        $subCategory->category_id = $request->category_id;
        $subCategory->name = $request->name;
        $subCategory->description = $request->description;
        $subCategory->save();

        if ($request->icon) {
            $subCategory->addMediaFromRequest('icon')->toMediaCollection(MediaCollection::ICON);
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('Sub-category updated successfully.')
            ->withData([
                'sub_category' => $subCategory,
            ])
            ->build();
    }

    /**
     * Toggle the specified resource's is_active status.
     *
     * @param SubCategory $subCategory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleActiveStatus(SubCategory $subCategory)
    {
        $subCategory->is_active = !$subCategory->is_active;
        $subCategory->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Sub-category active status updated successfully.')
            ->withData([
                'sub_category' => $subCategory,
            ])
            ->build();
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\SubCategory $subCategory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(SubCategory $subCategory)
    {
        $subCategory->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param \App\Models\SubCategory $subCategory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restore(SubCategory $subCategory)
    {
        $subCategory->restore();

        return ResponseBuilder::asSuccess()
            ->withMessage('Sub-category restored successfully.')
            ->withData([
                'sub_category' => $subCategory,
            ])
            ->build();
    }
}
