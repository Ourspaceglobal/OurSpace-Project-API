<?php

use App\Http\Controllers\User\AccountController;
use App\Http\Controllers\User\ApartmentAmenityController;
use App\Http\Controllers\User\ApartmentContactController;
use App\Http\Controllers\User\ApartmentController;
use App\Http\Controllers\User\ApartmentFavoriteController;
use App\Http\Controllers\User\ApartmentGalleryController;
use App\Http\Controllers\User\ApartmentGalleryMediaController;
use App\Http\Controllers\User\ApartmentKycController;
use App\Http\Controllers\User\ApartmentLocationController;
use App\Http\Controllers\User\ApartmentRentalController;
use App\Http\Controllers\User\Auth\ForgotPasswordController;
use App\Http\Controllers\User\Auth\LoginController;
use App\Http\Controllers\User\Auth\RegisterController;
use App\Http\Controllers\User\Auth\ResetPasswordController;
use App\Http\Controllers\User\Auth\SocialAuthController;
use App\Http\Controllers\User\Auth\VerificationController;
use App\Http\Controllers\User\BankAccountController;
use App\Http\Controllers\User\CommentController;
use App\Http\Controllers\User\LandlordRequestController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\PaymentCardController;
use App\Http\Controllers\User\PaymentTransactionController;
use App\Http\Controllers\User\ReviewController;
use App\Http\Controllers\User\SupportTicketController;
use App\Http\Controllers\User\SupportTicketReplyController;
use App\Http\Controllers\User\ViewController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\WalletFundingRequestController;
use App\Http\Controllers\User\WalletHistoryController;
use App\Http\Controllers\User\WithdrawalRequestController;
use Illuminate\Support\Facades\Route;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);
Route::post('password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [ResetPasswordController::class, 'reset']);
Route::post('login/{socialApp}', [SocialAuthController::class, 'index'])->where('socialApp', 'google|facebook');

Route::middleware('auth:api_user')->group(function () {
    Route::post('logout', [LoginController::class, 'logout']);
    Route::post('logout-all', [LoginController::class, 'logoutOtherDevices']);
    Route::get('email/verify', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('email/resend', [VerificationController::class, 'resend']);

    Route::get('/verify', [AccountController::class, 'index']);
    Route::post('/verify', [LoginController::class, 'validateTwofaCode']);
    Route::post('/verify/resend', [LoginController::class, 'getNewSecondFactorCode']);

    Route::middleware(['auth.2fa', 'unblocked'])->group(function () {
        Route::get('/', [AccountController::class, 'index']);
        Route::patch('/update', [AccountController::class, 'update']);
        Route::post('/update/avatar', [AccountController::class, 'updateAvatar']);
        Route::post('/update/password', [AccountController::class, 'updatePassword']);
        Route::post('/update/2fa', [AccountController::class, 'updateTwofa']);

        Route::apiResource('bank-accounts', BankAccountController::class);

        Route::prefix('landlord-requests')->group(function () {
            Route::get('', [LandlordRequestController::class, 'index']);
            Route::post('', [LandlordRequestController::class, 'store']);
            Route::get('{landlordRequest}', [LandlordRequestController::class, 'show']);
            Route::patch('{landlordRequest}/close', [LandlordRequestController::class, 'close']);
        });

        Route::apiResource('support-tickets', SupportTicketController::class)->except([
            'destroy',
        ]);
        Route::apiResource('support-tickets.replies', SupportTicketReplyController::class)->only([
            'index',
            'store',
            'show',
        ]);

        Route::apiResource('comments', CommentController::class)->only([
            'store',
            'update',
            'destroy',
        ]);

        Route::patch('withdrawal-requests/{withdrawalRequest}/close', [WithdrawalRequestController::class, 'close']);
        Route::apiResource('withdrawal-requests', WithdrawalRequestController::class)->only([
            'index',
            'store',
            'show',
        ]);

        Route::patch(
            'wallet-funding-requests/{walletFundingRequest}/close',
            [WalletFundingRequestController::class, 'close']
        );
        Route::apiResource('wallet-funding-requests', WalletFundingRequestController::class)->only([
            'index',
            'store',
            'show',
        ]);

        Route::get('wallet-history', [WalletHistoryController::class, 'index']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/read-all', [NotificationController::class, 'readAll']);
        Route::patch('notifications/{notification}', [NotificationController::class, 'read']);

        Route::get('apartment-favorites', [ApartmentFavoriteController::class, 'index']);
        Route::post('apartment-favorites/{apartment}', [ApartmentFavoriteController::class, 'store']);

        Route::get('apartments/{apartment}/contact', [ApartmentContactController::class, 'index']);
        Route::post('apartments/{apartment}/contact', [ApartmentContactController::class, 'store']);

        Route::get('apartments/{apartment}/location', [ApartmentLocationController::class, 'index']);
        Route::post('apartments/{apartment}/location', [ApartmentLocationController::class, 'store']);

        Route::patch('apartments/{apartment}/active', [ApartmentController::class, 'toggleActiveStatus']);
        Route::patch('apartments/{apartment}/restore', [ApartmentController::class, 'restore'])->withTrashed();
        Route::post('apartments/{apartment}/duplicate', [ApartmentController::class, 'duplicate'])->withTrashed();
        Route::apiResource('apartments', ApartmentController::class);

        Route::post(
            'apartments/{apartment}/galleries/{gallery}/images',
            [ApartmentGalleryMediaController::class, 'store']
        );
        Route::delete(
            'apartments/{apartment}/galleries/{gallery}/images/{media:uuid}',
            [ApartmentGalleryMediaController::class, 'destroy']
        );
        Route::apiResource('apartments.galleries', ApartmentGalleryController::class);

        Route::apiResource('apartments.amenities', ApartmentAmenityController::class)->only([
            'index',
            'store',
            'destroy',
        ]);

        Route::post('wallet/add-funds-with-paystack', [WalletController::class, 'addFundsWithPaystack']);

        Route::patch('payment-cards/{paymentCard}/primary', [PaymentCardController::class, 'togglePrimaryStatus']);
        Route::apiResource('payment-cards', PaymentCardController::class)->only([
            'index',
            'show',
            'destroy',
        ]);

        Route::get('payment-transactions', [PaymentTransactionController::class, 'index']);
        Route::get('payment-transactions/{paymentTransaction}', [PaymentTransactionController::class, 'show']);

        Route::prefix('apartment-rentals')->group(function () {
            Route::get('', [ApartmentRentalController::class, 'index']);
            Route::get('/landlord', [ApartmentRentalController::class, 'myRentals']);
            Route::post('{apartment}', [ApartmentRentalController::class, 'payWithPaystack']);
            Route::get('{apartmentRental}', [ApartmentRentalController::class, 'show']);
            Route::patch('{apartmentRental}/autorenewal', [ApartmentRentalController::class, 'toggleAutorenewal']);
            Route::post('{apartmentRental}/reminder', [ApartmentRentalController::class, 'sendReminder']);
            Route::patch('{apartmentRental}/check-in', [ApartmentRentalController::class, 'markCheckInDate']);
            Route::patch('{apartmentRental}/check-out', [ApartmentRentalController::class, 'markCheckOutDate']);
            Route::post('{apartmentRental}/cancel-booking', [ApartmentRentalController::class, 'cancelBooking']);
        });

        Route::get('apartments/{apartment}/kycs', [ApartmentKycController::class, 'index']);
        Route::post('apartments/{apartment}/kycs', [ApartmentKycController::class, 'store']);
        Route::post('apartments/{apartment}/kycs-enroll', [ApartmentKycController::class, 'enroll']);
        Route::delete('apartments/{apartment}/kycs/{systemApartmentKyc}', [ApartmentKycController::class, 'destroy']);

        Route::apiResource('reviews', ReviewController::class);

        Route::get('views', [ViewController::class, 'index']);
    });
});
