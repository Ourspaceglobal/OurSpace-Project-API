<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreReviewRequest;
use App\Http\Requests\User\UpdateReviewRequest;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $reviews = $request->user()->reviews()
            ->with('model')
            ->latest()
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Reviews fetched successfully.')
            ->withData([
                'reviews' => $reviews,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreReviewRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreReviewRequest $request)
    {
        $user = $request->user();

        $review = new Review();
        $review->user()->associate($user);
        $review->model_id = $request->model_id;
        $review->model_type = $request->model_type;
        $review->comment = $request->comment;
        $review->rating = $request->rating;
        $review->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Review stored successfully')
            ->withData([
                'review' => $review->unsetRelation('user'),
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Review $review
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Review $review)
    {
        $review->load([
            'model',
        ]);

        return ResponseBuilder::asSuccess()
            ->withMessage('Review fetched successfully')
            ->withData([
                'review' => $review,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateReviewRequest $request
     * @param \App\Models\Review $review
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $review->comment = $request->comment;
        $review->rating = $request->rating;
        $review->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Review updated successfully.')
            ->withData([
                'review' => $review,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Review $review
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $review->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
