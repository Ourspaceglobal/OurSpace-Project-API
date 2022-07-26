<?php

namespace App\Http\Controllers\User;

use App\Enums\MediaCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreSupportTicketRequest;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

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
        $user = $request->user();

        $supportTickets = $user->supportTickets()->latest()->paginate();

        return ResponseBuilder::asSuccess()
            ->withMessage('Support tickets fetched successfully.')
            ->withData([
                'support_tickets' => $supportTickets,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSupportTicketRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreSupportTicketRequest $request)
    {
        DB::beginTransaction();

        $supportTicket = new SupportTicket();
        $supportTicket->user_id = $request->user()->id;
        $supportTicket->subject = $request->subject;
        $supportTicket->message = $request->message;
        $supportTicket->save();

        if ($request->attachments) {
            $supportTicket->addMultipleMediaFromRequest(['attachments'])
                ->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection(MediaCollection::ATTACHMENT);
                });
        }

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Support ticket submitted successfully.')
            ->withData([
                'support_ticket' => $supportTicket->unsetRelation('user'),
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
        $this->authorize('view', $supportTicket);

        $supportTicket->attachments = $supportTicket->attachments();

        return ResponseBuilder::asSuccess()
            ->withMessage('Support ticket fetched successfully.')
            ->withData([
                'support_ticket' => $supportTicket->load('replies'),
            ])
            ->build();
    }

    /**
     * Update (close) the specified resource in storage.
     *
     * @param \App\Models\SupportTicket $supportTicket
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(SupportTicket $supportTicket)
    {
        $this->authorize('update', $supportTicket);

        $supportTicket->is_open = false;
        $supportTicket->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Support ticket closed successfully.')
            ->withData([
                'support_ticket' => $supportTicket->unsetRelation('replies'),
            ])
            ->build();
    }
}
