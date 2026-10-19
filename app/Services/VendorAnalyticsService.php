<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use App\Models\VendorProfile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VendorAnalyticsService
{
    /**
     * Compute comprehensive reporting metrics for a vendor profile.
     */
    public function getAnalytics(VendorProfile $vendor, array $filters = []): array
    {
        $dateRange = $filters['date_range'] ?? 'all';
        $eventId = ! empty($filters['event_id']) ? (int) $filters['event_id'] : null;
        $statusFilter = $filters['status'] ?? 'all';
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        // Resolve date bounds
        [$startDate, $endDate] = $this->resolveDateBounds($dateRange, $fromDate, $toDate);

        // Fetch vendor events
        $eventsQuery = Event::query()
            ->where('vendor_profile_id', $vendor->id)
            ->with(['ticketTypes']);

        if ($eventId) {
            $eventsQuery->where('id', $eventId);
        }

        $allVendorEvents = Event::query()
            ->where('vendor_profile_id', $vendor->id)
            ->select('id', 'title', 'slug', 'event_date')
            ->orderBy('title')
            ->get();

        $events = $eventsQuery->orderBy('event_date', 'desc')->get();

        // Base query for vendor orders
        $ordersQuery = Order::query()
            ->where('vendor_profile_id', $vendor->id)
            ->with(['event', 'user', 'items.ticketType']);

        if ($eventId) {
            $ordersQuery->where('event_id', $eventId);
        }

        if ($startDate) {
            $ordersQuery->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $ordersQuery->where('created_at', '<=', $endDate);
        }

        // Apply status filter if specified
        if ($statusFilter === 'paid') {
            $ordersQuery->where('status', 'paid')->where('total', '>', 0);
        } elseif ($statusFilter === 'free') {
            $ordersQuery->where('status', 'paid')->where(function ($q) {
                $q->where('total', '<=', 0)->orWhere('payment_provider', 'free');
            });
        } elseif ($statusFilter === 'refunded') {
            $ordersQuery->where('status', 'refunded');
        } elseif ($statusFilter === 'cancelled') {
            $ordersQuery->where('status', 'cancelled');
        }

        $filteredOrders = $ordersQuery->latest('created_at')->get();

        // Compute overall metrics
        $overall = $this->computeOverallMetrics($filteredOrders, $events);

        // Compute event-wise breakdown
        $eventSummaries = $this->computeEventSummaries($events, $filteredOrders);

        // Compute ticket category-wise aggregate breakdown
        $categoryBreakdown = $this->computeCategoryBreakdown($events, $filteredOrders);

        // Compute daily trend data for chart visualization
        $trendData = $this->computeTrendData($filteredOrders, $startDate, $endDate);

        // Top event & category highlights
        $topEvent = collect($eventSummaries)->sortByDesc('gross_revenue')->first();
        $topCategory = collect($categoryBreakdown)->sortByDesc('gross_revenue')->first();

        // Event-wise chart data
        $eventChartData = [
            'event_ids' => collect($eventSummaries)->pluck('event_id')->values()->all(),
            'labels' => collect($eventSummaries)->pluck('title')->map(fn ($t) => strlen($t) > 22 ? substr($t, 0, 20) . '...' : $t)->values()->all(),
            'full_labels' => collect($eventSummaries)->pluck('title')->values()->all(),
            'gross_revenue' => collect($eventSummaries)->pluck('gross_revenue')->values()->all(),
            'net_revenue' => collect($eventSummaries)->pluck('net_revenue')->values()->all(),
            'tickets_sold' => collect($eventSummaries)->pluck('total_sold')->values()->all(),
            'occupancy_rate' => collect($eventSummaries)->pluck('occupancy_rate')->values()->all(),
        ];

        // Category breakdown donut chart data
        $sortedCats = collect($categoryBreakdown)->sortByDesc('gross_revenue')->values();
        $catChartData = [
            'labels' => $sortedCats->pluck('category_name')->all(),
            'gross_revenue' => $sortedCats->pluck('gross_revenue')->all(),
            'tickets_sold' => $sortedCats->pluck('total_sold')->all(),
        ];

        // Status inventory distribution data
        $inventoryChartData = [
            'labels' => [
                __('Paid'),
                __('Free'),
                __('Available'),
                __('Refunded'),
                __('Cancelled'),
            ],
            'counts' => [
                $overall['paid_tickets_count'],
                $overall['free_tickets_count'],
                $overall['available_tickets_count'],
                $overall['refunded_tickets_count'],
                $overall['cancelled_tickets_count'],
            ],
        ];

        $formattedOrders = $filteredOrders->take(30)->map(function ($order) {
            $net = $order->status === 'paid' ? max(0, (float)$order->total - (float)$order->refunded_amount - (float)$order->platform_fee) : 0.00;
            return [
                'id' => $order->id,
                'code' => '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'event_title' => $order->event?->title ?? '—',
                'event_slug' => $order->event?->slug ?? '',
                'tier_name' => $order->items->first()?->ticketType?->name ?? __('General Admission'),
                'attendee_name' => $order->user?->name ?? __('Guest Buyer'),
                'attendee_email' => $order->user?->email ?? '',
                'quantity' => (int) $order->items->sum('quantity'),
                'gross' => (float) $order->total,
                'gross_formatted' => '$' . number_format((float) $order->total, 2),
                'fee' => (float) $order->platform_fee,
                'fee_formatted' => '-$' . number_format((float) $order->platform_fee, 2),
                'net' => (float) $net,
                'net_formatted' => '$' . number_format((float) $net, 2),
                'status' => $order->status,
                'date_formatted' => ($order->paid_at ?: $order->created_at)?->format('M j, Y') ?? '—',
            ];
        })->values()->all();

        return [
            'vendor' => $vendor,
            'filters' => [
                'date_range' => $dateRange,
                'event_id' => $eventId,
                'status' => $statusFilter,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'start_date_resolved' => $startDate?->toDateString(),
                'end_date_resolved' => $endDate?->toDateString(),
            ],
            'all_events' => $allVendorEvents,
            'overall' => $overall,
            'event_summaries' => $eventSummaries,
            'category_breakdown' => $categoryBreakdown,
            'trend_data' => $trendData,
            'top_event' => $topEvent,
            'top_category' => $topCategory,
            'event_chart_data' => $eventChartData,
            'cat_chart_data' => $catChartData,
            'inventory_chart_data' => $inventoryChartData,
            'recent_orders' => $filteredOrders->take(30),
            'formatted_orders' => $formattedOrders,
        ];
    }

    /**
     * Compute overall KPI cards.
     */
    private function computeOverallMetrics(Collection $orders, Collection $events): array
    {
        $paidOrders = $orders->filter(fn ($o) => $o->status === 'paid');
        $refundedOrders = $orders->filter(fn ($o) => $o->status === 'refunded');
        $cancelledOrders = $orders->filter(fn ($o) => $o->status === 'cancelled');

        $grossRevenue = (float) $paidOrders->sum('total');
        $refundedAmount = (float) $refundedOrders->sum(fn ($o) => (float) ($o->refunded_amount > 0 ? $o->refunded_amount : $o->total));
        $platformFees = (float) $paidOrders->sum('platform_fee');

        // Net revenue accounts for refunds, cancellations, and platform fees
        $netRevenue = max(0.0, round($grossRevenue - $refundedAmount - $platformFees, 2));

        $paidTicketsCount = 0;
        $freeTicketsCount = 0;
        $refundedTicketsCount = 0;
        $cancelledTicketsCount = 0;

        foreach ($orders as $order) {
            $orderQty = (int) $order->items->sum('quantity');

            if ($order->status === 'paid') {
                if ((float) $order->total > 0 && $order->payment_provider !== 'free') {
                    $paidTicketsCount += $orderQty;
                } else {
                    $freeTicketsCount += $orderQty;
                }
            } elseif ($order->status === 'refunded') {
                $refundedTicketsCount += $orderQty;
            } elseif ($order->status === 'cancelled') {
                $cancelledTicketsCount += $orderQty;
            }
        }

        $totalTicketsSold = $paidTicketsCount + $freeTicketsCount;

        // Sum event capacities
        $totalCapacity = 0;
        foreach ($events as $event) {
            $cap = (int) ($event->capacity ?: $event->ticketTypes->sum('quantity'));
            $totalCapacity += $cap;
        }

        $availableTickets = max(0, $totalCapacity - $totalTicketsSold);
        $occupancyRate = $totalCapacity > 0 ? round(($totalTicketsSold / $totalCapacity) * 100, 1) : 0.0;

        $paidOrdersWithMoney = $paidOrders->filter(fn ($o) => (float) $o->total > 0)->count();
        $averageOrderValue = $paidOrdersWithMoney > 0 ? round($grossRevenue / $paidOrdersWithMoney, 2) : 0.0;

        return [
            'gross_revenue' => $grossRevenue,
            'refunded_amount' => $refundedAmount,
            'platform_fees' => $platformFees,
            'net_revenue' => $netRevenue,
            'total_orders' => $orders->count(),
            'paid_orders_count' => $paidOrders->count(),
            'free_orders_count' => $orders->filter(fn ($o) => $o->status === 'paid' && ((float) $o->total <= 0 || $o->payment_provider === 'free'))->count(),
            'refunded_orders_count' => $refundedOrders->count(),
            'cancelled_orders_count' => $cancelledOrders->count(),
            'paid_tickets_count' => $paidTicketsCount,
            'free_tickets_count' => $freeTicketsCount,
            'total_tickets_sold' => $totalTicketsSold,
            'refunded_tickets_count' => $refundedTicketsCount,
            'cancelled_tickets_count' => $cancelledTicketsCount,
            'total_capacity' => $totalCapacity,
            'available_tickets_count' => $availableTickets,
            'occupancy_rate' => $occupancyRate,
            'average_order_value' => $averageOrderValue,
            'currency' => config('events.currency', 'USD'),
            'platform_fee_percent' => config('events.platform_fee_percentage', 5.0),
        ];
    }

    /**
     * Compute event-wise sales summaries.
     */
    private function computeEventSummaries(Collection $events, Collection $allFilteredOrders): array
    {
        $summaries = [];

        foreach ($events as $event) {
            $eventOrders = $allFilteredOrders->filter(fn ($o) => (int) $o->event_id === (int) $event->id);

            $paidOrders = $eventOrders->filter(fn ($o) => $o->status === 'paid');
            $refundedOrders = $eventOrders->filter(fn ($o) => $o->status === 'refunded');
            $cancelledOrders = $eventOrders->filter(fn ($o) => $o->status === 'cancelled');

            $grossRevenue = (float) $paidOrders->sum('total');
            $refundedAmount = (float) $refundedOrders->sum(fn ($o) => (float) ($o->refunded_amount > 0 ? $o->refunded_amount : $o->total));
            $platformFees = (float) $paidOrders->sum('platform_fee');
            $netRevenue = max(0.0, round($grossRevenue - $refundedAmount - $platformFees, 2));

            $paidTickets = 0;
            $freeTickets = 0;
            $refundedTickets = 0;
            $cancelledTickets = 0;

            foreach ($eventOrders as $order) {
                $qty = (int) $order->items->sum('quantity');
                if ($order->status === 'paid') {
                    if ((float) $order->total > 0 && $order->payment_provider !== 'free') {
                        $paidTickets += $qty;
                    } else {
                        $freeTickets += $qty;
                    }
                } elseif ($order->status === 'refunded') {
                    $refundedTickets += $qty;
                } elseif ($order->status === 'cancelled') {
                    $cancelledTickets += $qty;
                }
            }

            $totalSold = $paidTickets + $freeTickets;
            $capacity = (int) ($event->capacity ?: $event->ticketTypes->sum('quantity'));
            $available = max(0, $capacity - $totalSold);
            $occupancyRate = $capacity > 0 ? round(($totalSold / $capacity) * 100, 1) : 0.0;

            // Category breakdown for this specific event
            $categoryBreakdown = [];
            foreach ($event->ticketTypes as $ticketType) {
                $typeAllocated = (int) $ticketType->quantity;

                // Match order items for this ticket type
                $typePaidTickets = 0;
                $typeFreeTickets = 0;
                $typeRefundedTickets = 0;
                $typeCancelledTickets = 0;
                $typeGross = 0.0;

                foreach ($eventOrders as $order) {
                    $matchItems = $order->items->filter(fn ($it) => (int) $it->ticket_type_id === (int) $ticketType->id);
                    foreach ($matchItems as $item) {
                        $itemQty = (int) $item->quantity;
                        $itemRevenue = $itemQty * (float) $item->unit_price;

                        if ($order->status === 'paid') {
                            if ((float) $item->unit_price > 0 && $order->payment_provider !== 'free') {
                                $typePaidTickets += $itemQty;
                                $typeGross += $itemRevenue;
                            } else {
                                $typeFreeTickets += $itemQty;
                            }
                        } elseif ($order->status === 'refunded') {
                            $typeRefundedTickets += $itemQty;
                        } elseif ($order->status === 'cancelled') {
                            $typeCancelledTickets += $itemQty;
                        }
                    }
                }

                $typeTotalSold = $typePaidTickets + $typeFreeTickets;
                $typeAvailable = max(0, $typeAllocated - $typeTotalSold);
                $feeRate = (float) config('events.platform_fee_percentage', 5.0) / 100;
                $typeFees = round($typeGross * $feeRate, 2);
                $typeNet = max(0.0, round($typeGross - $typeFees, 2));
                $typeOccupancy = $typeAllocated > 0 ? round(($typeTotalSold / $typeAllocated) * 100, 1) : 0.0;

                $categoryBreakdown[] = [
                    'ticket_type_id' => $ticketType->id,
                    'name' => $ticketType->name,
                    'price' => (float) $ticketType->price,
                    'currency' => $ticketType->currency ?: 'USD',
                    'allocated' => $typeAllocated,
                    'paid_tickets' => $typePaidTickets,
                    'free_tickets' => $typeFreeTickets,
                    'total_sold' => $typeTotalSold,
                    'available' => $typeAvailable,
                    'refunded_tickets' => $typeRefundedTickets,
                    'cancelled_tickets' => $typeCancelledTickets,
                    'gross_revenue' => $typeGross,
                    'platform_fees' => $typeFees,
                    'net_revenue' => $typeNet,
                    'occupancy_rate' => $typeOccupancy,
                ];
            }

            $summaries[] = [
                'event_id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'status' => $event->status,
                'event_date' => $event->event_date?->format('Y-m-d'),
                'event_date_formatted' => $event->event_date?->translatedFormat('M j, Y'),
                'is_date_confirmed' => (bool) $event->is_date_confirmed,
                'event_type' => $event->event_type,
                'venue_name' => $event->venue_name,
                'city' => $event->city,
                'capacity' => $capacity,
                'paid_tickets' => $paidTickets,
                'free_tickets' => $freeTickets,
                'total_sold' => $totalSold,
                'refunded_tickets' => $refundedTickets,
                'cancelled_tickets' => $cancelledTickets,
                'available_tickets' => $available,
                'occupancy_rate' => $occupancyRate,
                'gross_revenue' => $grossRevenue,
                'refunded_amount' => $refundedAmount,
                'platform_fees' => $platformFees,
                'net_revenue' => $netRevenue,
                'orders_count' => $eventOrders->count(),
                'categories' => $categoryBreakdown,
            ];
        }

        return $summaries;
    }

    /**
     * Compute ticket category breakdown across all vendor events.
     */
    private function computeCategoryBreakdown(Collection $events, Collection $allFilteredOrders): array
    {
        $breakdown = [];

        foreach ($events as $event) {
            $eventOrders = $allFilteredOrders->filter(fn ($o) => (int) $o->event_id === (int) $event->id);

            foreach ($event->ticketTypes as $ticketType) {
                $typeAllocated = (int) $ticketType->quantity;
                $typePaidTickets = 0;
                $typeFreeTickets = 0;
                $typeRefundedTickets = 0;
                $typeCancelledTickets = 0;
                $typeGross = 0.0;

                foreach ($eventOrders as $order) {
                    $matchItems = $order->items->filter(fn ($it) => (int) $it->ticket_type_id === (int) $ticketType->id);
                    foreach ($matchItems as $item) {
                        $itemQty = (int) $item->quantity;
                        $itemRevenue = $itemQty * (float) $item->unit_price;

                        if ($order->status === 'paid') {
                            if ((float) $item->unit_price > 0 && $order->payment_provider !== 'free') {
                                $typePaidTickets += $itemQty;
                                $typeGross += $itemRevenue;
                            } else {
                                $typeFreeTickets += $itemQty;
                            }
                        } elseif ($order->status === 'refunded') {
                            $typeRefundedTickets += $itemQty;
                        } elseif ($order->status === 'cancelled') {
                            $typeCancelledTickets += $itemQty;
                        }
                    }
                }

                $typeTotalSold = $typePaidTickets + $typeFreeTickets;
                $typeAvailable = max(0, $typeAllocated - $typeTotalSold);
                $feeRate = (float) config('events.platform_fee_percentage', 5.0) / 100;
                $typeFees = round($typeGross * $feeRate, 2);
                $typeNet = max(0.0, round($typeGross - $typeFees, 2));
                $typeOccupancy = $typeAllocated > 0 ? round(($typeTotalSold / $typeAllocated) * 100, 1) : 0.0;

                $breakdown[] = [
                    'ticket_type_id' => $ticketType->id,
                    'event_id' => $event->id,
                    'event_title' => $event->title,
                    'category_name' => $ticketType->name,
                    'price' => (float) $ticketType->price,
                    'currency' => $ticketType->currency ?: 'USD',
                    'allocated' => $typeAllocated,
                    'paid_tickets' => $typePaidTickets,
                    'free_tickets' => $typeFreeTickets,
                    'total_sold' => $typeTotalSold,
                    'available' => $typeAvailable,
                    'refunded_tickets' => $typeRefundedTickets,
                    'cancelled_tickets' => $typeCancelledTickets,
                    'gross_revenue' => $typeGross,
                    'platform_fees' => $typeFees,
                    'net_revenue' => $typeNet,
                    'occupancy_rate' => $typeOccupancy,
                ];
            }
        }

        return $breakdown;
    }

    /**
     * Compute daily timeline trend points for visual charting.
     */
    private function computeTrendData(Collection $orders, ?Carbon $startDate, ?Carbon $endDate): array
    {
        $start = $startDate ?: now()->subDays(13)->startOfDay();
        $end = $endDate ?: now()->endOfDay();

        // Limit chart points to max 31 days for clean display
        $diffDays = $start->diffInDays($end);
        if ($diffDays > 31) {
            $start = $end->copy()->subDays(30)->startOfDay();
        }

        $trend = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $dayKey = $current->format('Y-m-d');
            $dayLabel = $current->translatedFormat('M j');

            $dayOrders = $orders->filter(function ($o) use ($dayKey) {
                $d = $o->paid_at ?: $o->created_at ?: $o->reserved_at;
                return $d && $d->format('Y-m-d') === $dayKey;
            });
            $dayPaid = $dayOrders->filter(fn ($o) => $o->status === 'paid');

            $dayGross = (float) $dayPaid->sum('total');
            $dayFees = (float) $dayPaid->sum('platform_fee');
            $dayRefunded = (float) $dayOrders->filter(fn ($o) => $o->status === 'refunded')->sum(fn ($o) => (float) ($o->refunded_amount > 0 ? $o->refunded_amount : $o->total));
            $dayNet = max(0.0, round($dayGross - $dayFees - $dayRefunded, 2));

            $dayTickets = 0;
            foreach ($dayPaid as $order) {
                $dayTickets += (int) $order->items->sum('quantity');
            }

            $trend[] = [
                'date' => $dayKey,
                'label' => $dayLabel,
                'gross_revenue' => $dayGross,
                'net_revenue' => $dayNet,
                'tickets_sold' => $dayTickets,
                'orders_count' => $dayOrders->count(),
            ];

            $current->addDay();
        }

        return $trend;
    }

    /**
     * Stream CSV export for event-wise sales summaries.
     */
    public function exportSummaryCsv(VendorProfile $vendor, array $filters = []): StreamedResponse
    {
        $analytics = $this->getAnalytics($vendor, $filters);
        $summaries = $analytics['event_summaries'];
        $currency = $analytics['overall']['currency'];
        $filename = 'event_sales_summary_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($summaries, $currency) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'Event ID',
                'Event Title',
                'Event Date',
                'Status',
                'Total Capacity',
                'Paid Tickets Sold',
                'Free Tickets Sold',
                'Total Tickets Sold',
                'Available Tickets',
                'Occupancy Rate (%)',
                "Gross Revenue ({$currency})",
                "Refunded Amount ({$currency})",
                "Platform Fees ({$currency})",
                "Net Vendor Revenue ({$currency})",
                'Total Orders',
            ]);

            foreach ($summaries as $s) {
                fputcsv($handle, [
                    $s['event_id'],
                    $s['title'],
                    $s['event_date'],
                    ucfirst($s['status']),
                    $s['capacity'],
                    $s['paid_tickets'],
                    $s['free_tickets'],
                    $s['total_sold'],
                    $s['available_tickets'],
                    $s['occupancy_rate'] . '%',
                    number_format($s['gross_revenue'], 2, '.', ''),
                    number_format($s['refunded_amount'], 2, '.', ''),
                    number_format($s['platform_fees'], 2, '.', ''),
                    number_format($s['net_revenue'], 2, '.', ''),
                    $s['orders_count'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Stream CSV export for individual ticket orders.
     */
    public function exportOrdersCsv(VendorProfile $vendor, array $filters = []): StreamedResponse
    {
        $analytics = $this->getAnalytics($vendor, $filters);
        $orders = $analytics['recent_orders'];
        $currency = $analytics['overall']['currency'];
        $filename = 'ticket_orders_report_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($orders, $currency) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'Order ID',
                'Order Date',
                'Event Title',
                'Ticket Category',
                'Buyer Name',
                'Buyer Email',
                'Quantity',
                "Total Amount ({$currency})",
                "Platform Fee ({$currency})",
                "Net Vendor Payout ({$currency})",
                'Status',
                'Payment Provider',
            ]);

            foreach ($orders as $order) {
                $categoryName = $order->items->map(fn ($it) => $it->ticketType?->name ?? 'General Admission')->implode(', ');
                $totalQty = $order->items->sum('quantity');
                $net = $order->status === 'paid' ? max(0, (float) $order->total - (float) $order->refunded_amount - (float) $order->platform_fee) : 0.00;

                fputcsv($handle, [
                    '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    $order->created_at?->format('Y-m-d H:i:s'),
                    $order->event?->title ?? 'N/A',
                    $categoryName,
                    $order->user?->name ?? 'Guest Buyer',
                    $order->user?->email ?? '',
                    $totalQty,
                    number_format($order->total, 2, '.', ''),
                    number_format($order->platform_fee, 2, '.', ''),
                    number_format($net, 2, '.', ''),
                    ucfirst($order->status),
                    strtoupper($order->payment_provider ?: 'standard'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Stream CSV export for ticket category performance breakdown.
     */
    public function exportCategoriesCsv(VendorProfile $vendor, array $filters = []): StreamedResponse
    {
        $analytics = $this->getAnalytics($vendor, $filters);
        $categories = $analytics['category_breakdown'];
        $currency = $analytics['overall']['currency'];
        $filename = 'ticket_categories_report_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($categories, $currency) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'Event Title',
                'Category / Tier Name',
                "Unit Price ({$currency})",
                'Allocated Capacity',
                'Paid Tickets Sold',
                'Free Tickets Sold',
                'Total Tickets Sold',
                'Available Remaining',
                'Refunded Tickets',
                'Cancelled Tickets',
                'Occupancy Rate (%)',
                "Gross Sales ({$currency})",
                "Platform Fees ({$currency})",
                "Net Revenue ({$currency})",
            ]);

            foreach ($categories as $cat) {
                fputcsv($handle, [
                    $cat['event_title'],
                    $cat['category_name'],
                    number_format($cat['price'], 2, '.', ''),
                    $cat['allocated'],
                    $cat['paid_tickets'],
                    $cat['free_tickets'],
                    $cat['total_sold'],
                    $cat['available'],
                    $cat['refunded_tickets'],
                    $cat['cancelled_tickets'],
                    $cat['occupancy_rate'] . '%',
                    number_format($cat['gross_revenue'], 2, '.', ''),
                    number_format($cat['platform_fees'], 2, '.', ''),
                    number_format($cat['net_revenue'], 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Helper to resolve start and end timestamps from preset or custom date range.
     */
    private function resolveDateBounds(string $preset, ?string $from, ?string $to): array
    {
        $now = now();

        switch ($preset) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'last_7_days':
                return [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()];
            case 'last_30_days':
                return [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()];
            case 'this_month':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            case 'last_month':
                return [
                    $now->copy()->subMonth()->startOfMonth(),
                    $now->copy()->subMonth()->endOfMonth(),
                ];
            case 'this_year':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear()];
            case 'custom':
                $start = $from ? Carbon::parse($from)->startOfDay() : null;
                $end = $to ? Carbon::parse($to)->endOfDay() : null;
                return [$start, $end];
            case 'all':
            default:
                return [null, null];
        }
    }
}
