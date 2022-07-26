<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\WalletHistory;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class WalletHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $walletHistory = $request->user()
            ->walletHistory()
            ->latest()
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Wallet History fetched successfully.')
            ->withData([
                'wallet_history' => $walletHistory,
            ])
            ->build();
    }
}
