<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(private CheckoutService $checkoutService)
    {
    }

    public function handle(Request $request): Response
    {
        $secret = config('services.stripe.webhook_secret');
        if (!$secret) {
            return response('Stripe webhook secret not configured', 400);
        }

        $payload = $request->getContent();
        $sigHeader = (string) $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException|\UnexpectedValueException $e) {
            return response('Invalid webhook payload', 400);
        }

        $type = $event->type ?? '';
        $object = $event->data->object ?? null;

        if (!$object) {
            return response('No object payload', 400);
        }

        switch ($type) {
            case 'checkout.session.completed':
                $orderId = (int) ($object->metadata->order_id ?? $object->client_reference_id ?? 0);
                $order = Order::query()->find($orderId);
                if ($order) {
                    $order->update([
                        'checkout_session_id' => $object->id ?? $order->checkout_session_id,
                        'payment_intent_id' => $object->payment_intent ?? $order->payment_intent_id,
                    ]);

                    $this->checkoutService->markPaid($order, (string) ($object->payment_intent ?? ''));
                }
                break;

            case 'checkout.session.async_payment_failed':
            case 'checkout.session.expired':
                $orderId = (int) ($object->metadata->order_id ?? $object->client_reference_id ?? 0);
                $order = Order::query()->find($orderId);
                if ($order && $order->status === 'pending') {
                    $this->checkoutService->cancel($order, 'Stripe checkout failed or expired.');
                }
                break;
        }

        return response('ok', 200);
    }
}
