<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MediaCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSupportTicketReplyRequest;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class SupportTicketReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param SupportTicket $supportTicket
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(SupportTicket $supportTicket)
    {
        $supportTicketReplies = $supportTicket->replies()
            ->with([
                'user' => fn($query) => $query->select(['id', 'first_name', 'last_name', 'email', 'phone_number']),
            ])
            ->withCount([
                'media as attachments_count',
            ])
            ->get();

        return ResponseBuilder::asSuccess()
            ->withMessage('Support ticket replies fetched successfully.')
            ->withData([
                'support_ticket_replies' => $supportTicketReplies,
            ])
            ->build();
    }

    /**
     * Store a listing of the resource.
     *
     * @param StoreSupportTicketReplyRequest $request
     * @param SupportTicket $supportTicket
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreSupportTicketReplyRequest $request, SupportTicket $supportTicket)
    {
        DB::beginTransaction();

        $supportTicketReply = new SupportTicketReply();
        $supportTicketReply->supportTicket()->associate($supportTicket);
        $supportTicketReply->user()->associate($request->user());
        $supportTicketReply->message = $request->message;
        $supportTicketReply->save();

        if ($request->attachments) {
            $supportTicketReply->addMultipleMediaFromRequest(['attachments'])
                ->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection(MediaCollection::ATTACHMENT);
                });
        }

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Support ticket reply stored successfully.')
            ->withData([
                'support_ticket_reply' => $supportTicketReply->unsetRelation('user'),
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param SupportTicket $supportTicket
     * @param SupportTicketReply $reply
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(SupportTicket $supportTicket, SupportTicketReply $reply)
    {
        abort_if($supportTicket->id !== $reply->support_ticket_id, 403);

        $reply->attachments = $reply->attachments();

        return ResponseBuilder::asSuccess()
            ->withMessage('Support ticket reply fetched successfully.')
            ->withData([
                'support_ticket_reply' => $reply,
            ])
            ->build();
    }
}
