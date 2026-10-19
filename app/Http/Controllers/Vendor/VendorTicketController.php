<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorTicketController extends Controller
{
    public function create(Event $event)
    {
        Gate::authorize('update', $event);
        return view('vendor.tickets.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        Gate::authorize('update', $event);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tier' => ['nullable', 'string', 'in:bronze,silver,gold,diamond,custom'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'quantity' => ['required', 'integer', 'min:0'],
            'sales_start' => ['nullable', 'date'],
            'sales_end' => ['nullable', 'date', 'after_or_equal:sales_start'],
            'is_active' => ['nullable'],
        ]);

        $data['event_id'] = $event->id;
        $data['currency'] = strtoupper($request->input('currency') ?: 'USD');
        $data['tier'] = $data['tier'] ?? 'custom';
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $ticketType = TicketType::create($data);

        if ($event->event_type !== 'paid') {
            $event->update([
                'event_type' => 'paid',
                'base_price' => $ticketType->price,
            ]);
        } elseif ($event->base_price === null || $ticketType->price < $event->base_price) {
            $event->update(['base_price' => $ticketType->price]);
        }

        return redirect()->route('vendor.events.edit', $event)->with('status', __('Ticket type created successfully.'));
    }

    public function edit(Event $event, TicketType $ticketType)
    {
        Gate::authorize('update', $event);
        abort_if((int)$ticketType->event_id !== (int)$event->id, 404);

        return view('vendor.tickets.edit', compact('event', 'ticketType'));
    }

    public function update(Request $request, Event $event, TicketType $ticketType)
    {
        Gate::authorize('update', $event);
        abort_if((int)$ticketType->event_id !== (int)$event->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tier' => ['nullable', 'string', 'in:bronze,silver,gold,diamond,custom'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'quantity' => ['required', 'integer', 'min:0'],
            'sales_start' => ['nullable', 'date'],
            'sales_end' => ['nullable', 'date', 'after_or_equal:sales_start'],
            'is_active' => ['nullable'],
        ]);

        $data['currency'] = strtoupper($request->input('currency') ?: ($ticketType->currency ?: 'USD'));
        $data['tier'] = $data['tier'] ?? $ticketType->tier ?? 'custom';
        $data['is_active'] = $request->boolean('is_active');

        $ticketType->update($data);

        // Recalculate event lowest base price
        $lowestPrice = TicketType::where('event_id', $event->id)
            ->where('is_active', true)
            ->min('price');

        if ($lowestPrice !== null) {
            $event->update(['base_price' => $lowestPrice]);
        }

        return redirect()->route('vendor.events.edit', $event)->with('status', __('Ticket tier updated successfully.'));
    }

    public function destroy(Event $event, TicketType $ticketType)
    {
        Gate::authorize('update', $event);
        abort_if((int)$ticketType->event_id !== (int)$event->id, 404);

        if ($ticketType->sold > 0) {
            // Cannot delete if there are sold tickets; safely deactivate
            $ticketType->update(['is_active' => false]);
            return redirect()->route('vendor.events.edit', $event)
                ->with('status', __('Ticket tier has existing sales, so it was deactivated rather than deleted.'));
        }

        $ticketType->delete();

        // Update event base price
        $lowestPrice = TicketType::where('event_id', $event->id)
            ->where('is_active', true)
            ->min('price');

        $event->update(['base_price' => $lowestPrice ?: 0]);

        return redirect()->route('vendor.events.edit', $event)->with('status', __('Ticket tier deleted successfully.'));
    }

    public function generateDefaults(Request $request, Event $event)
    {
        Gate::authorize('update', $event);

        $defaultTiers = [
            [
                'name' => 'Bronze Admission',
                'tier' => 'bronze',
                'price' => 25.00,
                'quantity' => 100,
                'description' => 'Standard event admission, general area access and seating.',
            ],
            [
                'name' => 'Silver Pass',
                'tier' => 'silver',
                'price' => 50.00,
                'quantity' => 75,
                'description' => 'Preferred seating, express check-in lane, complimentary welcome beverage.',
            ],
            [
                'name' => 'Gold VIP',
                'tier' => 'gold',
                'price' => 100.00,
                'quantity' => 50,
                'description' => 'VIP priority seating, dedicated lounge access, event gift bag & refreshments.',
            ],
            [
                'name' => 'Diamond Elite',
                'tier' => 'diamond',
                'price' => 200.00,
                'quantity' => 25,
                'description' => 'Ultra-VIP all-access pass, front-row reserved seat, backstage meet & greet, exclusive hospitality.',
            ],
        ];

        $currency = config('events.currency', 'USD');
        $createdCount = 0;

        foreach ($defaultTiers as $tierDef) {
            // Check if tier already exists for this event
            $exists = TicketType::where('event_id', $event->id)
                ->where(function ($q) use ($tierDef) {
                    $q->where('tier', $tierDef['tier'])
                      ->orWhere('name', $tierDef['name']);
                })
                ->exists();

            if (!$exists) {
                TicketType::create([
                    'event_id' => $event->id,
                    'name' => $tierDef['name'],
                    'tier' => $tierDef['tier'],
                    'price' => $tierDef['price'],
                    'currency' => $currency,
                    'quantity' => $tierDef['quantity'],
                    'description' => $tierDef['description'],
                    'is_active' => true,
                ]);
                $createdCount++;
            }
        }

        if ($event->event_type !== 'paid') {
            $event->update([
                'event_type' => 'paid',
                'base_price' => 25.00,
            ]);
        }

        return redirect()->route('vendor.events.edit', $event)
            ->with('status', __(':count default ticket tiers generated successfully.', ['count' => $createdCount]));
    }
}
