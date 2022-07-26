<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\QueryBuilder\BasicUserInfoExtract;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $supportTickets = QueryBuilder::for(SupportTicket::class)
            ->allowedFilters([
                'reference',
                'user_id',
                'is_open',
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'is_open',
                'updated_at',
                'created_at',
            ])
            ->allowedIncludes([
                AllowedInclude::custom('user', new BasicUserInfoExtract()),
            ])
            ->withCount([
                'media as attachments_count',
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Support tickets fetched successfully.')
            ->withData([
                'support_tickets' => $supportTickets,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\SupportTicket $supportTicket
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->attachments = $supportTicket->attachments();

        return ResponseBuilder::asSuccess()
            ->withMessage('Support ticket fetched successfully.')
            ->withData([
                'support_ticket' => $supportTicket->load('replies'),
            ])
            ->build();
    }
}
