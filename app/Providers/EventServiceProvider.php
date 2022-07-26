<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \Illuminate\Auth\Events\Registered::class => [
            \Illuminate\Auth\Listeners\SendEmailVerificationNotification::class,
            \App\Listeners\User\SendWelcomeNotification::class,
        ],
        \Illuminate\Auth\Events\Verified::class => [
            \App\Listeners\User\SendEmailVerifiedNotification::class,
        ],
        \App\Events\User\ConfirmResetPassword::class => [
            \App\Listeners\User\SendResetPasswordNotification::class,
        ],
        \App\Events\Admin\Registered::class => [
            \App\Listeners\Admin\SendWelcomeNotification::class,
        ],
        \App\Events\Admin\Verified::class => [
            \App\Listeners\Admin\SendEmailVerifiedNotification::class,
        ],
        \App\Events\Admin\ConfirmResetPassword::class => [
            \App\Listeners\Admin\SendResetPasswordNotification::class,
        ],
        \App\Events\User\ChangeTenantToLandlord::class => [
            \App\Listeners\User\UpdateUserTypeToLandlord::class,
        ],
        \App\Events\User\WalletHistoryRecorder::class => [
            \App\Listeners\User\RecordWalletHistory::class,
        ],
        \App\Events\User\TempWalletModifier::class => [
            \App\Listeners\User\ModifyTempWalletHistory::class,
        ],
        \App\Events\User\ViewLogger::class => [
            \App\Listeners\User\LogView::class,
        ],
        \App\Events\User\ApartmentVerification::class => [
            \App\Listeners\User\UpdateApartmentVerifiedStatus::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Admin::observe(\App\Observers\AdminObserver::class);
        \App\Models\LandlordRequest::observe(\App\Observers\LandlordRequestObserver::class);
        \App\Models\SupportTicket::observe(\App\Observers\SupportTicketObserver::class);
        \App\Models\SupportTicketReply::observe(\App\Observers\SupportTicketReplyObserver::class);
        \App\Models\Post::observe(\App\Observers\PostObserver::class);
        \App\Models\WithdrawalRequest::observe(\App\Observers\WithdrawalRequestObserver::class);
        \App\Models\WalletFundingRequest::observe(\App\Observers\WalletFundingRequestObserver::class);
        \App\Models\Apartment::observe(\App\Observers\ApartmentObserver::class);
        \App\Models\Category::observe(\App\Observers\CategoryObserver::class);
        \App\Models\SubCategory::observe(\App\Observers\SubCategoryObserver::class);
        \App\Models\ApartmentLocation::observe(\App\Observers\ApartmentLocationObserver::class);
        \App\Models\ApartmentRental::observe(\App\Observers\ApartmentRentalObserver::class);
        \App\Models\Review::observe(\App\Observers\ReviewObserver::class);
    }
}
