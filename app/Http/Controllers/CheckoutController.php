<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TicketType;
use App\Repositories\EventRepository;
use App\Services\CheckoutService;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Stripe\StripeClient;

class CheckoutController extends Controller
{
    public function __construct(
        private EventRepository $eventRepository,
        private CheckoutService $checkoutService,
        private ReferralService $referralService
    ) {
    }

    public function createSession(Request $request, string $slug)
    {
        $event = $this->eventRepository->findBySlug($slug);
        abort_unless($event, 404);
        $this->authorize('view', $event);

        $hasTicketTypes = $event->ticketTypes->count() > 0;

        $rules = [
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ];
        if ($hasTicketTypes) {
            $rules['ticket_type_id'] = ['required', 'integer', 'exists:ticket_types,id'];
        }
        $request->validate($rules);

        if ($hasTicketTypes) {
            $ticketType = $event->ticketTypes->firstWhere('id', $request->integer('ticket_type_id'));
            abort_unless($ticketType, 404);
        } else {
            if ($event->event_type !== 'free') {
                throw ValidationException::withMessages([
                    'buy' => 'No ticket type is configured for this paid event yet.',
                ]);
            }

            $ticketType = TicketType::firstOrCreate(
                ['event_id' => $event->id, 'name' => 'General Admission'],
                [
                    'price' => 0,
                    'currency' => 'USD',
                    'quantity' => $event->capacity ?: 100000,
                    'sold' => 0,
                ]
            );
        }

        $isFreeFlow = $event->event_type === 'free' || (float) $ticketType->price <= 0;
        $referrerId = $this->referralService->getReferrerForEvent($request, $event);

        if ($isFreeFlow) {
            $order = $this->checkoutService->createOrder(
                user: $request->user(),
                event: $event,
                ticketTypeId: $ticketType->id,
                quantity: $request->integer('quantity'),
                referredByUserId: $referrerId,
                referralSource: $referrerId ? 'share' : null,
                paymentProvider: 'free',
                status: 'paid',
            );

            $ticket = $order->items->first()?->tickets()->first();
            if ($ticket) {
                return redirect()->route('account.tickets.show', $ticket)->with('status', 'Registration complete. Ticket issued.');
            }

            return redirect()->route('account.tickets.index')->with('status', 'Registration complete.');
        }

        $secret = config('services.stripe.secret');
        if (!$secret) {
            throw ValidationException::withMessages([
                'buy' => 'Stripe is not configured yet. Please add STRIPE_SECRET to continue.',
            ]);
        }

        $order = $this->checkoutService->createOrder(
            user: $request->user(),
            event: $event,
            ticketTypeId: $ticketType->id,
            quantity: $request->integer('quantity'),
            referredByUserId: $referrerId,
            referralSource: $referrerId ? 'share' : null,
            paymentProvider: 'stripe',
            status: 'pending',
        );

        try {
            $stripe = new StripeClient($secret);
            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($ticketType->currency ?: 'usd'),
                        'unit_amount' => (int) round(((float) $ticketType->price) * 100),
                        'product_data' => [
                            'name' => "{$event->title} - {$ticketType->name}",
                            'description' => 'Event ticket',
                        ],
                    ],
                    'quantity' => (int) $request->integer('quantity'),
                ]],
                'client_reference_id' => (string) $order->id,
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'event_id' => (string) $event->id,
                    'ticket_type_id' => (string) $ticketType->id,
                ],
                'success_url' => route('checkout.success', ['order' => $order->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkout.cancel', ['order' => $order->id]),
            ]);

            $order->update([
                'checkout_session_id' => $session->id,
            ]);

            return redirect()->away($session->url);
        } catch (\Throwable $e) {
            $this->checkoutService->cancel($order, $e->getMessage());

            return back()->withErrors([
                'buy' => 'Unable to start checkout: ' . $e->getMessage(),
            ]);
        }
    }

    public function success(Request $request, Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        if ($order->status !== 'paid' && $order->payment_provider === 'stripe' && config('services.stripe.secret')) {
            $sessionId = $request->query('session_id', $order->checkout_session_id);
            if ($sessionId) {
                try {
                    $stripe = new StripeClient(config('services.stripe.secret'));
                    $session = $stripe->checkout->sessions->retrieve($sessionId, []);

                    if (($session->payment_status ?? null) === 'paid') {
                        $order = $this->checkoutService->markPaid($order, (string) ($session->payment_intent ?? ''));
                    }
                } catch (\Throwable) {
                    // Webhook may finalize payment asynchronously; ignore transient lookup failures.
                }
            }
        }

        $ticket = $order->items()->with('tickets')->first()?->tickets->first();
        if ($ticket) {
            return redirect()->route('account.tickets.show', $ticket)->with('status', 'Payment successful. Ticket issued.');
        }

        return redirect()->route('account.tickets.index')->with('status', 'Payment successful.');
    }

    public function cancel(Request $request, Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        if ($order->status === 'pending') {
            $this->checkoutService->cancel($order, 'Payment was cancelled by user.');
        }

        $eventSlug = $order->event?->slug;
        $target = $eventSlug ? route('events.show', $eventSlug) : route('search');

        return redirect($target)->withErrors([
            'buy' => 'Checkout was cancelled.',
        ]);
    }
}
