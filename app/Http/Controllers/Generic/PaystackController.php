<?php

namespace App\Http\Controllers\Generic;

use App\Enums\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\WebhookEvent;
use App\Services\PaystackService;
use Yabacon\Paystack\Event;

class PaystackController extends Controller
{
    /**
     * Constructor.
     *
     * @param PaystackService $paystackService
     * @param WebhookEvent $webhookEvent
     */
    public function __construct(public PaystackService $paystackService, public WebhookEvent $webhookEvent)
    {
        //
    }

    /**
     * Handle the webhook.
     *
     * @return void
     */
    public function webhook()
    {
        // Retrieve the request's body and parse it as JSON.
        $event = Event::capture();
        http_response_code(200);

        // Log event in our DB.
        $webhookEvent = new $this->webhookEvent();
        $webhookEvent->payment_gateway = PaymentGateway::PAYSTACK;
        $webhookEvent->log = $event->raw;
        $webhookEvent->save();

        // Verify that the signature matches one of your keys.
        $my_keys = [
            'live' => config('paystack.secret_key'),
        ];

        if (!$event->discoverOwner($my_keys)) {
            exit();
        }

        $this->paystackService->process(json_decode($webhookEvent->log)->data->reference);

        return response('Webhook task was successful.');
    }
}
