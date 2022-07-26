<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\ApartmentBookingController;
use App\Http\Controllers\Admin\ApartmentController;
use App\Http\Controllers\Admin\ApartmentDurationController;
use App\Http\Controllers\Admin\ApartmentRentalController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\Auth\VerificationController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\DashboardStatisticController;
use App\Http\Controllers\Admin\LandlordRequestController;
use App\Http\Controllers\Admin\LocalGovernmentController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PaymentTransactionController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PushNotificationController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\SupportTicketReplyController;
use App\Http\Controllers\Admin\SystemApartmentKycController;
use App\Http\Controllers\Admin\SystemDataController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WalletFundingRequestController;
use App\Http\Controllers\Admin\WithdrawalRequestController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'login']);
Route::post('password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [ResetPasswordController::class, 'reset']);

Route::middleware('auth:api_admin')->group(function () {
    Route::post('logout', [LoginController::class, 'logout']);
    Route::post('logout-all', [LoginController::class, 'logoutOtherDevices']);
    Route::get('email/verify', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('email/resend', [VerificationController::class, 'resend']);

    Route::get('verify', [AccountController::class, 'index']);
    Route::post('verify', [LoginController::class, 'validateTwofaCode']);
    Route::post('verify/resend', [LoginController::class, 'getNewSecondFactorCode']);

    Route::get('my-permissions', [AccountController::class, 'getPermissions']);

    Route::middleware(['auth.2fa', 'unblocked'])->group(function () {
        Route::get('', [AccountController::class, 'index']);
        Route::patch('update', [AccountController::class, 'update']);
        Route::post('update/avatar', [AccountController::class, 'updateAvatar']);
        Route::post('update/password', [AccountController::class, 'updatePassword']);
        Route::post('update/2fa', [AccountController::class, 'updateTwofa']);

        Route::get('statistics', [DashboardStatisticController::class, 'index']);

        Route::middleware('can:manage states')->group(function () {
            Route::patch('states/{state}/restore', [StateController::class, 'restore'])->withTrashed();
            Route::apiResource('states', StateController::class);
        });

        Route::middleware('can:manage cities')->group(function () {
            Route::patch('cities/{city}/restore', [CityController::class, 'restore'])->withTrashed();
            Route::apiResource('cities', CityController::class);
        });

        Route::middleware('can:manage local governments')->group(function () {
            Route::patch('local-governments/{local_government}/restore', [LocalGovernmentController::class, 'restore'])
                ->withTrashed();
            Route::apiResource('local-governments', LocalGovernmentController::class);
        });

        Route::middleware('can:manage categories')->group(function () {
            Route::patch('categories/{category}/active', [CategoryController::class, 'toggleActiveStatus']);
            Route::patch('categories/{category}/restore', [CategoryController::class, 'restore'])->withTrashed();
            Route::apiResource('categories', CategoryController::class);
        });

        Route::middleware('can:manage sub-categories')->group(function () {
            Route::patch('sub-categories/{subCategory}/active', [SubCategoryController::class, 'toggleActiveStatus']);
            Route::patch('sub-categories/{subCategory}/restore', [SubCategoryController::class, 'restore'])
                ->withTrashed();
            Route::apiResource('sub-categories', SubCategoryController::class)->parameters([
                'sub-categories' => 'subCategory',
            ]);
        });

        Route::middleware('can:manage amenities')->group(function () {
            Route::patch('amenities/{amenity}/active', [AmenityController::class, 'toggleActiveStatus']);
            Route::patch('amenities/{amenity}/restore', [AmenityController::class, 'restore'])->withTrashed();
            Route::apiResource('amenities', AmenityController::class);
        });

        Route::middleware('can:manage system data')->group(function () {
            Route::apiResource('system-data', SystemDataController::class)->only([
                'index',
                'show',
                'update',
            ]);
        });

        Route::middleware('can:manage system apartment kycs')->group(function () {
            Route::patch(
                'system-apartment-kycs/{system_apartment_kyc}/restore',
                [SystemApartmentKycController::class, 'restore']
            )->withTrashed();
            Route::apiResource('system-apartment-kycs', SystemApartmentKycController::class);
        });

        Route::middleware('can:manage landlord requests')->prefix('landlord-requests')->group(function () {
            Route::get('', [LandlordRequestController::class, 'index']);
            Route::get('{landlordRequest}', [LandlordRequestController::class, 'show']);
            Route::patch('{landlordRequest}/approve', [LandlordRequestController::class, 'approve']);
            Route::patch('{landlordRequest}/decline', [LandlordRequestController::class, 'decline']);
        });

        Route::middleware('can:manage support tickets')->group(function () {
            Route::apiResource('support-tickets', SupportTicketController::class)->only(['index', 'show']);
            Route::apiResource('support-tickets.replies', SupportTicketReplyController::class)->only([
                'index',
                'store',
                'show',
            ]);
        });

        Route::middleware('can:manage posts')->group(function () {
            Route::patch('posts/{post}/publication', [PostController::class, 'togglePublicationStatus']);
            Route::patch('posts/{post}/restore', [PostController::class, 'restore'])->withTrashed();
            Route::apiResource('posts', PostController::class);

            Route::get('comments/{comment}/replies', [CommentController::class, 'index']);
            Route::patch('comments/{comment}/approval', [CommentController::class, 'toggleApprovalStatus']);
        });

        Route::middleware('can:manage withdrawal requests')->prefix('withdrawal-requests')->group(function () {
            Route::get('', [WithdrawalRequestController::class, 'index']);
            Route::get('{withdrawalRequest}', [WithdrawalRequestController::class, 'show']);
            Route::patch('{withdrawalRequest}/approve', [WithdrawalRequestController::class, 'approve']);
            Route::patch('{withdrawalRequest}/decline', [WithdrawalRequestController::class, 'decline']);
        });

        Route::middleware('can:manage access control list')->group(function () {
            Route::get('permissions', [PermissionController::class, 'index']);
            Route::apiResource('roles', RoleController::class);
        });

        Route::middleware('can:manage admins')->group(function () {
            Route::patch('admins/{admin}/restore', [AdminController::class, 'restore'])->withTrashed();
            Route::patch('admins/{admin}/toggle-role', [AdminController::class, 'toggleRole']);
            Route::patch('admins/{admin}/block', [AdminController::class, 'toggleBlockStatus']);
            Route::apiResource('admins', AdminController::class);
        });

        Route::middleware('can:manage users')->group(function () {
            Route::patch('users/{user}/block', [UserController::class, 'toggleBlockStatus']);
            Route::apiResource('users', UserController::class)->only([
                'index',
                'show',
            ]);
        });

        Route::middleware('can:manage wallet funding requests')->prefix('wallet-funding-requests')->group(function () {
            Route::get('', [WalletFundingRequestController::class, 'index']);
            Route::get('{walletFundingRequest}', [WalletFundingRequestController::class, 'show']);
            Route::patch('{walletFundingRequest}/approve', [WalletFundingRequestController::class, 'approve']);
            Route::patch('{walletFundingRequest}/decline', [WalletFundingRequestController::class, 'decline']);
        });

        Route::middleware('can:manage push notifications')->group(function () {
            Route::patch(
                'push-notifications/{pushNotification}/restore',
                [PushNotificationController::class, 'restore']
            )->withTrashed();
            Route::apiResource('push-notifications', PushNotificationController::class);
        });

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/read-all', [NotificationController::class, 'readAll']);
        Route::patch('notifications/{notification}', [NotificationController::class, 'read']);

        Route::middleware('can:manage apartment durations')->group(function () {
            Route::patch(
                'apartment-durations/{apartment_duration}/restore',
                [ApartmentDurationController::class, 'restore']
            )->withTrashed();
            Route::apiResource('apartment-durations', ApartmentDurationController::class);
        });

        Route::middleware('can:manage payment transactions')->group(function () {
            Route::apiResource('payment-transactions', PaymentTransactionController::class)->only([
                'index',
                'show',
            ]);
        });

        Route::middleware('can:manage apartments')->group(function () {
            Route::patch('apartments/{apartment}/active', [ApartmentController::class, 'toggleActiveStatus']);
            Route::patch('apartments/{apartment}/verify', [ApartmentController::class, 'toggleVerifiedStatus']);
            Route::patch('apartments/{apartment}/feature', [ApartmentController::class, 'toggleFeaturedStatus']);
            Route::apiResource('apartments', ApartmentController::class)->only([
                'index',
                'show',
            ]);
        });

        Route::middleware('can:manage apartment bookings')->group(function () {
            Route::get('apartment-bookings', [ApartmentBookingController::class, 'index']);
        });

        Route::middleware('can:manage apartment rentals')->group(function () {
            Route::get('apartment-rentals', [ApartmentRentalController::class, 'index']);
            Route::patch(
                'apartment-rentals/{apartmentRental}/terminate',
                [ApartmentRentalController::class, 'terminate']
            );
        });
    });
});
