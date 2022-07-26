<?php

namespace App\Http\Controllers\User;

use App\Actions\CertifyApartmentRentalAction;
use App\Enums\MediaCollection;
use App\Enums\PaymentGateway;
use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Events\User\WalletHistoryRecorder;
use App\Exceptions\InsufficientWalletFundsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\RentApartmentWithPaystackRequest;
use App\Models\Apartment;
use App\Models\ApartmentRental;
use App\Models\PaymentCard;
use App\Models\PaymentTransaction;
use App\Models\SystemData;
use App\Notifications\User\ApartmentRentalExpiryReminderNotification;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ApartmentRentalController extends Controller
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

        $apartmentRentals = $user->apartmentRentals()
            ->with([
                'apartment' => fn ($query) => $query
                    ->select([
                        'id', 'name', 'slug', 'price', 'description', 'rating', 'apartment_duration_id',
                    ])
                    ->with([
                        'amenities:id,name',
                        'location',
                    ])
                    ->withCount([
                        'reviews' => fn ($query) => $query->approved(),
                    ]),
                'paymentTransaction',
            ])
            ->when(
                (bool) $request->bookings,
                fn ($query) => $query->bookings(),
                fn ($query) => $query->where('started_at', '<=', now()),
            )
            ->when($request->date_added, function ($query, $date) {
                $formattedDate = explode('/', $date);

                throw_if(
                    count($formattedDate) <> 2,
                    InvalidFormatException::class,
                    'Incorrect format for date filter. Expects month/year.'
                );

                $month = head($formattedDate);
                $year = last($formattedDate);

                $query->whereRaw('year(apartment_rentals.created_at) = ?', [$year])
                    ->whereRaw('month(apartment_rentals.created_at) = ?', [$month]);
            })
            ->latest('started_at')
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment rentals fetched successfully.')
            ->withData([
                'apartment_rentals' => $apartmentRentals,
            ])
            ->build();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function myRentals(Request $request)
    {
        $user = $request->user();

        $apartmentRentals = $user->myApartmentRentals()
            ->with([
                'apartment' => fn ($query) => $query
                    ->select([
                        'id', 'name', 'slug', 'price', 'description', 'rating',
                    ])
                    ->with([
                        'amenities:id,name',
                        'location',
                    ])
                    ->withCount([
                        'reviews' => fn ($query) => $query->approved(),
                    ]),
                'user:id,first_name,last_name,finder,email,phone_number',
                'paymentTransaction',
            ])
            ->when(
                (bool) $request->bookings,
                fn ($query) => $query->bookings(),
                fn ($query) => $query->where('started_at', '<=', now()),
            )
            ->when($request->date_added, function ($query, $date) {
                $formattedDate = explode('/', $date);

                throw_if(
                    count($formattedDate) <> 2,
                    InvalidFormatException::class,
                    'Incorrect format for date filter. Expects month/year.'
                );

                $month = head($formattedDate);
                $year = last($formattedDate);

                $query->whereRaw('year(apartment_rentals.created_at) = ?', [$year])
                    ->whereRaw('month(apartment_rentals.created_at) = ?', [$month]);
            })
            ->latest('started_at')
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment rentals fetched successfully.')
            ->withData([
                'apartment_rentals' => $apartmentRentals,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param mixed $apartmentRental
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($apartmentRental)
    {
        $apartmentRental = ApartmentRental::query()
            ->with([
                'apartment' => fn ($query) => $query
                    ->select([
                        'id', 'apartment_duration_id', 'user_id', 'name', 'slug', 'price', 'description', 'rating',
                    ])
                    ->with([
                        'apartmentDuration:id,duration_in_days,period',
                        'amenities:id,name',
                        'location',
                        'reviews' => fn ($query) => $query->approved()->with('user:id,first_name,last_name'),
                        'user' => fn ($query) => $query
                            ->select([
                                'id', 'finder', 'first_name', 'last_name', 'email', 'phone_number',
                                'gender', 'country', 'state', 'date_of_birth', 'home_address', 'rating',
                            ]),
                    ]),
                'user' => fn ($query) => $query
                    ->select([
                        'id', 'finder', 'first_name', 'last_name', 'email', 'phone_number',
                        'gender', 'country', 'state', 'date_of_birth', 'home_address', 'rating',
                    ])
                    ->with([
                        'apartmentKycs' => fn ($query) => $query->with([
                            'systemApartmentKyc' => fn ($query) => $query
                                ->select(['id', 'datatype_id', 'name', 'description', 'is_required'])
                                ->with('datatype:id,name')
                        ])
                    ]),
                'paymentTransaction',
            ])
            ->findOrFail($apartmentRental);

        $this->authorize('view', $apartmentRental);

        $apartmentRental->user->apartmentKycs->each(fn($apartmentKyc) =>
            $apartmentKyc->entry = $apartmentKyc->entry
                ?? $apartmentKyc->getMedia(MediaCollection::KYC)->first()?->original_url);

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment rental fetched successfully.')
            ->withData([
                'apartment_rental' => $apartmentRental,
            ])
            ->build();
    }

    /**
     * Toggle autorenewal status of the specified resource.
     *
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleAutorenewal(ApartmentRental $apartmentRental)
    {
        $this->authorize('view', $apartmentRental);

        $apartmentRental->is_autorenewal_active = !$apartmentRental->is_autorenewal_active;
        $apartmentRental->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment rental autorenewal status updated successfully.')
            ->withData([
                'apartment_rental' => $apartmentRental->withoutRelations(),
            ])
            ->build();
    }

    /**
     * Rent an apartment using Paystack.
     *
     * @param RentApartmentWithPaystackRequest $request
     * @param Apartment $apartment
     * @param PaystackService $paystackService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function payWithPaystack(
        RentApartmentWithPaystackRequest $request,
        Apartment $apartment,
        PaystackService $paystackService,
        CertifyApartmentRentalAction $certifyApartmentRentalAction
    ) {
        $this->authorize('create', [ApartmentRental::class, $apartment]);

        $user = $request->user();
        $bookingStartDate = $request->booking_start_date;

        $systemData = SystemData::query()->select(['title', 'content'])->get();
        $bookingPeriod = $systemData->firstWhere('title', 'Booking Period')->content;

        // Certify that the apartment can be rented
        $certifyApartmentRentalAction->execute($apartment, $user, $bookingPeriod, $bookingStartDate);

        DB::beginTransaction();

        // Calculate the service charge here
        $serviceCharge = $systemData->firstWhere('title', 'Service Charge')->content;
        $amountToPay = $apartment->price + (($serviceCharge / 100) * $apartment->price);

        // Create the payment transaction
        $paymentTransaction = new PaymentTransaction();
        $paymentTransaction->user()->associate($user);
        $paymentTransaction->model()->associate($apartment);
        $paymentTransaction->payment_purpose = PaymentPurpose::APARTMENTRENTAL;
        $paymentTransaction->payment_gateway = PaymentGateway::PAYSTACK;
        $paymentTransaction->amount = $amountToPay;
        $paymentTransaction->truthy_amount = $apartment->price;
        $paymentTransaction->currency = 'NGN';
        $paymentTransaction->reference = strtoupper('AR' . now()->timestamp . bin2hex(random_bytes(6)));
        $paymentTransaction->metadata = [
            'user_id' => $user->id,
            'user_type' => $user->getMorphClass(),
            'model_id' => $apartment->id,
            'model_type' => $apartment->getMorphClass(),
            'booking_start_date' => $bookingStartDate,
        ];
        $paymentTransaction->save();

        $data = [];
        $message = 'Request was successful.';

        if ($request->pay_with_wallet) {
            $newWalletBalance = $user->wallet_balance - $paymentTransaction->amount;

            if ($newWalletBalance < 0) {
                throw new InsufficientWalletFundsException();
            }

            $paymentTransaction->payment_gateway = PaymentGateway::WALLET;
            $paymentTransaction->payment_method = 'wallet';
            $paymentTransaction->status = PaymentStatus::SUCCESS;
            $paymentTransaction->narration = 'Approved';

            $user->wallet_balance = $newWalletBalance;
            $user->save();

            $paymentTransaction->save();

            event(new WalletHistoryRecorder(
                $user,
                $paymentTransaction,
                "₦{$paymentTransaction->amount} deducted from your wallet for apartment rent"
            ));

            \App\Services\User\ApartmentRentalService::serve($paymentTransaction);

            $data = [
                'status' => 'success',
                'reference' => $paymentTransaction->reference,
            ];

            $message = 'Wallet deducted and apartment rented successfully.';
        } elseif ($paymentCard = PaymentCard::find($request->payment_card_id)) {
            $transaction = $paystackService->getFactory()->transaction->charge([
                'reference' => $paymentTransaction->reference,
                'amount' => (int) ($paymentTransaction->amount * 100),
                'currency' => $paymentTransaction->currency,
                'email' => $paymentTransaction->user->email,
                'authorization_code' => $paymentCard->authorization_code,
            ]);

            if ($transaction->status) {
                $data = [
                    'status' => $transaction->data->status,
                    'reference' => $transaction->data->reference,
                ];
                $message = $transaction->data->gateway_response;
            } else {
                $message = $transaction->message;
            }
        } else {
            $transaction = $paystackService->getFactory()->transaction->initialize([
                'reference' => $paymentTransaction->reference,
                'amount' => (int) ($paymentTransaction->amount * 100),
                'currency' => $paymentTransaction->currency,
                'email' => $paymentTransaction->user->email,
                'callback_url' => $request->callbackUrl,
            ]);

            $data = [
                'authorization_url' => $transaction->data->authorization_url,
                'access_code' => $transaction->data->access_code,
                'reference' => $transaction->data->reference,
            ];

            $message = 'Payment intent generated successfully.';
        }

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage($message)
            ->withData($data)
            ->build();
    }

    /**
     * Send apartment rent expiry reminder to user.
     *
     * @param ApartmentRental $apartmentRental
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sendReminder(ApartmentRental $apartmentRental)
    {
        $this->authorize('sendReminder', $apartmentRental);

        abort_if(
            !$apartmentRental->is_active,
            403,
            'This rent is not active!'
        );

        abort_if(
            $apartmentRental->last_reminder_sent_at !== null
            && $apartmentRental->last_reminder_sent_at->addWeeks(1) > now(),
            Response::HTTP_TOO_EARLY,
            "Last reminder was sent {$apartmentRental->last_reminder_sent_at?->diffForHumans()}. "
            . 'Waiting period is 7 days.'
        );

        DB::beginTransaction();

        $apartmentRental->last_reminder_sent_at = now();
        $apartmentRental->save();

        $apartmentRental->user->notify(new ApartmentRentalExpiryReminderNotification($apartmentRental));

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Reminder sent successfully.')
            ->build();
    }

    /**
     * Mark 'check_in_date' of the specified resource.
     *
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function markCheckInDate(ApartmentRental $apartmentRental)
    {
        $this->authorize('update', $apartmentRental);

        abort_if(
            !$apartmentRental->is_active,
            403,
            'This rent is not active!'
        );

        abort_if(
            $apartmentRental->check_in_date,
            403,
            'You are checked-in already!'
        );

        DB::beginTransaction();

        $apartmentRental->check_in_date = now();
        $apartmentRental->save();

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment rental checked-in successfully.')
            ->withData([
                'apartment_rental' => $apartmentRental->withoutRelations(),
            ])
            ->build();
    }

    /**
     * Mark 'check_out_date' of the specified resource.
     *
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function markCheckOutDate(ApartmentRental $apartmentRental)
    {
        $this->authorize('update', $apartmentRental);

        abort_if(
            !$apartmentRental->is_active,
            403,
            'This rent is not active!'
        );

        abort_if(
            !$apartmentRental->check_in_date,
            403,
            'You have not checked-in yet!'
        );

        abort_if(
            $apartmentRental->check_out_date,
            403,
            'You have checked-out already!'
        );

        $apartmentRental->check_out_date = now();
        $apartmentRental->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment rental checked-out successfully.')
            ->withData([
                'apartment_rental' => $apartmentRental->withoutRelations(),
            ])
            ->build();
    }

    /**
     * Cancel booking and revert payment.
     *
     * @param Request $request
     * @param ApartmentRental $apartmentRental
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cancelBooking(Request $request, ApartmentRental $apartmentRental)
    {
        $user = $request->user();

        $this->authorize('update', $apartmentRental);

        abort_if(
            $apartmentRental->check_in_date,
            403,
            'You have checked-in already!'
        );

        abort_if(
            $apartmentRental->terminated_at,
            403,
            'Booking is terminated already!'
        );

        abort_if(
            $apartmentRental->is_active,
            403,
            'Only booked apartments can be cancelled!'
        );

        DB::beginTransaction();

        $bookingPaymentTransaction = $apartmentRental->paymentTransaction;

        // Get the "Booking Cancellation Penalty"
        $systemData = SystemData::query()->select(['title', 'content'])->get();
        $companyPenalty = $systemData->firstWhere('title', 'Booking Cancellation Penalty')->content;
        $landlordPenalty = $systemData->firstWhere('title', 'Booking Cancellation Penalty for Landlord')->content;

        $amountToPay = ($companyPenalty / 100) * $bookingPaymentTransaction->truthy_amount;

        // Create a new payment transaction
        $paymentTransaction = new PaymentTransaction();
        $paymentTransaction->user()->associate($user);
        $paymentTransaction->model()->associate($apartmentRental);
        $paymentTransaction->payment_purpose = PaymentPurpose::APARTMENTBOOKINGCANCELLATION;
        $paymentTransaction->payment_gateway = PaymentGateway::WALLET;
        $paymentTransaction->amount = $amountToPay;
        $paymentTransaction->truthy_amount = $amountToPay;
        $paymentTransaction->currency = 'NGN';
        $paymentTransaction->reference = strtoupper('ABC' . now()->timestamp . bin2hex(random_bytes(6)));
        $paymentTransaction->metadata = [
            'user_id' => $user->id,
            'user_type' => $user->getMorphClass(),
            'model_id' => $apartmentRental->id,
            'model_type' => $apartmentRental->getMorphClass(),
        ];
        $paymentTransaction->save();

        // Adjust the user's wallet
        $user->wallet_balance += $bookingPaymentTransaction->truthy_amount;
        $user->wallet_balance = $user->wallet_balance - $amountToPay;
        $user->save();

        event(new WalletHistoryRecorder(
            $user,
            $paymentTransaction,
            "₦{$paymentTransaction->amount} deducted from your wallet for apartment booking cancellation"
        ));

        // Adjust the rental to be terminated
        $apartmentRental->terminated_at = now();
        $apartmentRental->termination_reason = 'I want to cancel my booking.';
        $apartmentRental->save();

        // Adjust the landlord's temp_wallet_balance
        $landlord = $apartmentRental->apartment->user;
        $landlord->temp_wallet_balance -= $bookingPaymentTransaction->truthy_amount;
        $landlord->temp_wallet_balance += (($landlordPenalty / 100) * $bookingPaymentTransaction->truthy_amount);
        $landlord->save();

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Apartment booking cancelled successfully.')
            ->build();
    }
}
