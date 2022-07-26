<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MediaCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $categories = QueryBuilder::for(Category::class)
            ->allowedIncludes([
                AllowedInclude::count('subCategoriesCount', 'subCategories'),
                AllowedInclude::count('apartmentsCount', 'apartments'),
            ])
            ->allowedFilters([
                'name',
                'is_active',
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
            ->withMessage('Categories fetched successfully.')
            ->withData([
                'categories' => $categories,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCategoryRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = new Category();
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        if ($request->icon) {
            $category->addMediaFromRequest('icon')->toMediaCollection(MediaCollection::ICON);
        }

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Category created successfully.')
            ->withData([
                'category' => $category,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param mixed $category
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($category)
    {
        $category = QueryBuilder::for(Category::withTrashed()->where('id', $category))
            ->allowedIncludes([
                AllowedInclude::count('subCategoriesCount', 'subCategories'),
                AllowedInclude::count('apartmentsCount', 'apartments'),
            ])
            ->firstOrFail();

        return ResponseBuilder::asSuccess()
            ->withMessage('Category fetched successfully.')
            ->withData([
                'category' => $category,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCategoryRequest $request
     * @param \App\Models\Category $category
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        if ($request->icon) {
            $category->addMediaFromRequest('icon')->toMediaCollection(MediaCollection::ICON);
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('Category updated successfully.')
            ->withData([
                'category' => $category,
            ])
            ->build();
    }

    /**
     * Toggle the specified resource's is_active status.
     *
     * @param Category $category
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleActiveStatus(Category $category)
    {
        $category->is_active = !$category->is_active;
        $category->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Category active status updated successfully.')
            ->withData([
                'category' => $category,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Category $category
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param \App\Models\Category $category
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restore(Category $category)
    {
        $category->restore();

        return ResponseBuilder::asSuccess()
            ->withMessage('Category restored successfully.')
            ->withData([
                'category' => $category,
            ])
            ->build();
    }
}
