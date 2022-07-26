<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PaymentCard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class PaymentCardController extends Controller
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

        $paymentCards = $user->paymentCards()
            ->latest()
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Payment cards fetched successfully.')
            ->withData([
                'payment_cards' => $paymentCards,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\PaymentCard $paymentCard
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(PaymentCard $paymentCard)
    {
        $this->authorize('view', $paymentCard);

        return ResponseBuilder::asSuccess()
            ->withMessage('Payment cards fetched successfully.')
            ->withData([
                'payment_card' => $paymentCard,
            ])
            ->build();
    }

    /**
     * Toggle primary status of the specified resource.
     *
     * @param Request $request
     * @param \App\Models\PaymentCard $paymentCard
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function togglePrimaryStatus(Request $request, PaymentCard $paymentCard)
    {
        $this->authorize('update', $paymentCard);

        $user = $request->user();

        DB::beginTransaction();

        $paymentCard->is_primary = !$paymentCard->is_primary;
        $paymentCard->save();

        if ($paymentCard->is_primary) {
            $user->paymentCards()->where('id', '!=', $paymentCard->id)->update([
                'is_primary' => false,
            ]);
        }

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Payment card primary status updated successfully.')
            ->withData([
                'payment_card' => $paymentCard,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\PaymentCard $paymentCard
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(PaymentCard $paymentCard)
    {
        $this->authorize('delete', $paymentCard);

        $paymentCard->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
