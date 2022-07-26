<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\ApartmentRental;
use App\Models\PaymentTransaction;
use App\Models\Post;
use App\Models\SupportTicket;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class DashboardStatisticController extends Controller
{
    /**
     * Get all essential statistics for the application.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $statistics = [];

        // My notifications
        $statistics['my_notifications'] = [['unread' => $request->user()->notifications()->unread()->count()]];

        // Apartments
        $statistics['apartments'] = DB::table('apartments')
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(is_active = 0 or NULL) as inactive'),
                DB::raw('COUNT(is_verified = 0 or NULL) as unverified'),
                DB::raw('COUNT(is_featured = 0 or NULL) as unfeatured'),
            ])
            ->get();

        // Support tickets
        $supportTicketStats = DB::table('support_tickets')
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(is_open = 1 or NULL) as open'),
                DB::raw('COUNT(is_open = 0 or NULL) as closed'),
            ])
            ->get();
        $awaitingReply = SupportTicket::query()
            ->select(['id', 'is_open'])
            ->where('is_open', true)
            ->get()
            ->where('is_awaiting_reply', true)
            ->count();
        $supportTicketStats[0]->awaiting_reply = $awaitingReply;
        $statistics['support_tickets'] = $supportTicketStats;

        // Apartment rentals
        $apartmentRentals = DB::table('apartment_rentals')
            ->select([
                DB::raw('COUNT(*) as total_history'),
                DB::raw('COUNT(IF(terminated_at is not NULL, terminated_at, NULL)) as terminated_total'),
                DB::raw('COUNT(NOW() > expired_at or NULL) as expired_total'),
                DB::raw('COUNT(IF((expired_at > NOW() and terminated_at is NULL), expired_at, NULL)) as active_total'),
            ])
            ->get();
        $apartmentRentals[0]->latest_rental = ApartmentRental::query()
            ->select(['id', 'apartment_id', 'user_id', 'payment_transaction_id', 'started_at', 'expired_at'])
            ->with([
                'user:id,first_name,last_name,email',
                'apartment:id,name,slug',
                'paymentTransaction:id,reference,amount',
            ])
            ->active()
            ->orderBy('expired_at')
            ->limit(1)
            ->first();
        $statistics['apartment_rentals'] = $apartmentRentals;

        // Payment transactions
        $paymentTransactions = DB::table('payment_transactions')
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw("COUNT(status = 'pending' or NULL) as pending"),
                DB::raw("COUNT(status = 'success' or NULL) as success"),
                DB::raw("COUNT(status = 'fail' or NULL) as fail"),
                DB::raw("COUNT(payment_gateway = 'wallet' or NULL) as wallet_payments"),
                DB::raw("COUNT(payment_gateway = 'paystack' or NULL) as paystack_payments"),
                DB::raw("SUM(IF(status = 'success', amount, 0)) as amount_success"),
                DB::raw("SUM(IF(status = 'fail', amount, 0)) as amount_failed"),
            ])
            ->get();

        $paymentTransactions[0]->last_twelve_months_stats = DB::table('payment_transactions');
        for ($i = 12; $i >= 0; $i--) {
            $today = today();
            $date = $today->subMonths($i);
            $year = $date->format("Y");
            $month = $date->format("m");
            $alias = strtolower("{$date->shortMonthName}_{$year}");

            $paymentTransactions[0]->last_twelve_months_stats->addSelect(DB::raw(
                "COUNT(IF(year(created_at) = {$year} and month(created_at) = {$month}, created_at, NULL))
                as '{$alias}'",
            ));
            $paymentTransactions[0]->last_twelve_months_stats->addSelect(DB::raw(
                "SUM(
                    IF(year(created_at) = {$year} and month(created_at) = {$month} and status = 'success', amount, 0)
                ) as '{$alias}_success_amount'",
            ));
            $paymentTransactions[0]->last_twelve_months_stats->addSelect(DB::raw(
                "SUM(
                    IF(year(created_at) = {$year} and month(created_at) = {$month} and status = 'fail', amount, 0)
                ) as '{$alias}_fail_amount'",
            ));
        }
        $paymentTransactions[0]->last_twelve_months_stats = $paymentTransactions[0]->last_twelve_months_stats->get();
        $statistics['payment_transactions'] = $paymentTransactions;

        // Landlord requests
        $statistics['landlord_requests'] = DB::table('landlord_requests')
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw("COUNT(status = 'pending' or NULL) as pending"),
                DB::raw("COUNT(status = 'approved' or NULL) as approved"),
                DB::raw("COUNT(status = 'declined' or NULL) as declined"),
            ])
            ->get();

        // Withdrawal requests
        $statistics['withdrawal_requests'] = DB::table('withdrawal_requests')
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw("COUNT(status = 'pending' or NULL) as pending"),
                DB::raw("COUNT(status = 'approved' or NULL) as approved"),
                DB::raw("COUNT(status = 'declined' or NULL) as declined"),
                DB::raw("COUNT(status = 'closed' or NULL) as closed"),
            ])
            ->get();

        // Wallet funding requests
        $statistics['wallet_funding_requests'] = DB::table('wallet_funding_requests')
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw("COUNT(status = 'pending' or NULL) as pending"),
                DB::raw("COUNT(status = 'approved' or NULL) as approved"),
                DB::raw("COUNT(status = 'declined' or NULL) as declined"),
                DB::raw("COUNT(status = 'closed' or NULL) as closed"),
            ])
            ->get();

        // Users
        $users = DB::table('users')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("COUNT(type = 'landlord' or NULL) as total_landlords"),
                DB::raw("COUNT(type = 'tenant' or NULL) as total_tenants"),
            )
            ->get();
        $users[0]->newest_users = User::query()
            ->select(['first_name', 'last_name', 'email', 'wallet_balance'])
            ->orderBy('created_at')
            ->limit(5)
            ->get();
        $users[0]->richest_users = User::query()
            ->select(['first_name', 'last_name', 'email', 'wallet_balance'])
            ->orderBy('wallet_balance', 'DESC')
            ->limit(5)
            ->get();
        $statistics['users'] = $users;

        // Post
        $statistics['posts'] = DB::table('posts')
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(is_published = 0 or NULL) as draft'),
            )
            ->get();
        $statistics['posts'][0]->most_viewed_posts = Post::query()
            ->select(['id', 'title', 'slug', 'created_at'])
            ->withCount('views')
            ->without('tags')
            ->orderBy('views_count', 'DESC')
            ->limit(2)
            ->get();
        $statistics['posts'][0]->most_interesting_posts = Post::query()
            ->select(['id', 'title', 'slug', 'created_at'])
            ->withCount('comments')
            ->without('tags')
            ->orderBy('comments_count', 'DESC')
            ->limit(2)
            ->get();

        return ResponseBuilder::asSuccess()
            ->withMessage('Dashboard statistical data fetched successfully.')
            ->withData([
                'statistics' => $statistics,
            ])
            ->build();
    }
}
