<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Event Dates Announced – :title', ['title' => $event->title]) }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.6;
        }
        .wrapper {
            width: 100%;
            background-color: #f8fafc;
            padding: 40px 16px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: #064e3b;
            background: linear-gradient(135deg, #064e3b 0%, #047857 100%);
            padding: 32px 32px 28px;
            text-align: center;
            color: #ffffff;
        }
        .brand {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #a7f3d0;
            margin-bottom: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.3;
        }
        .content {
            padding: 32px;
        }
        .intro-banner {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            color: #065f46;
            font-size: 15px;
            font-weight: 600;
        }
        .event-banner-img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 24px;
            display: block;
        }
        .event-details-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 12px;
            font-size: 14px;
        }
        .detail-row:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            font-weight: 700;
            color: #64748b;
            min-width: 110px;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
            padding-top: 2px;
        }
        .detail-value {
            color: #0f172a;
            font-weight: 600;
            flex: 1;
        }
        .description-box {
            color: #475569;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 28px;
            padding: 16px;
            background: #ffffff;
            border-left: 3px solid #10b981;
            border-radius: 0 8px 8px 0;
        }
        .actions-box {
            text-align: center;
            margin-bottom: 24px;
        }
        .btn-primary {
            display: inline-block;
            background: #059669;
            color: #ffffff !important;
            padding: 14px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            margin: 6px;
            box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);
        }
        .btn-secondary {
            display: inline-block;
            background: #f1f5f9;
            color: #334155 !important;
            padding: 14px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin: 6px;
            border: 1px solid #cbd5e1;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 24px 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            {{-- Header --}}
            <div class="header">
                <div class="brand">{{ config('app.name', 'Huddle Events') }}</div>
                <h1>{{ __('Dates Announced!') }}</h1>
            </div>

            {{-- Main Content --}}
            <div class="content">
                <div class="intro-banner">
                    🎉 {{ __('Good news! The dates for :name have been announced.', ['name' => $event->title]) }}
                </div>

                @php
                    $heroImage = $event->media->where('type', 'image')->first();
                    $imageUrl = $heroImage ? $heroImage->url : null;
                    $eventDateStr = $event->event_date ? $event->event_date->format('l, F j, Y') : __('To Be Announced');
                    $startTimeStr = $event->start_time instanceof \Carbon\Carbon ? $event->start_time->format('g:i A') : (string) $event->start_time;
                    $endTimeStr = $event->end_time ? ($event->end_time instanceof \Carbon\Carbon ? $event->end_time->format('g:i A') : (string) $event->end_time) : null;
                    $timeDisplay = $startTimeStr . ($endTimeStr ? ' – ' . $endTimeStr : '');
                    $locationDisplay = implode(', ', array_filter([$event->venue_name, $event->address, $event->city, $event->country])) ?: __('Venue TBA');

                    if ($event->event_type === 'free') {
                        $priceDisplay = __('Free Admission');
                    } elseif ($event->ticketTypes->isNotEmpty()) {
                        $minPrice = $event->ticketTypes->min('price');
                        $maxPrice = $event->ticketTypes->max('price');
                        $priceDisplay = (float) $minPrice == (float) $maxPrice
                            ? '$' . number_format((float) $minPrice, 2)
                            : '$' . number_format((float) $minPrice, 2) . ' – $' . number_format((float) $maxPrice, 2);
                    } elseif ($event->base_price !== null) {
                        $priceDisplay = '$' . number_format((float) $event->base_price, 2);
                    } else {
                        $priceDisplay = __('To Be Announced');
                    }

                    $eventUrl = route('events.show', $event->slug);
                    $isBookingAvailable = $event->isBookingEligible();
                @endphp

                @if($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $event->title }}" class="event-banner-img">
                @endif

                <h2 style="margin: 0 0 16px; font-size: 20px; color: #0f172a; font-weight: 800;">
                    {{ $event->title }}
                </h2>

                <p style="margin: 0 0 20px; font-size: 14px; color: #475569;">
                    {{ __('The event is scheduled for :date at :location.', ['date' => $eventDateStr, 'location' => $locationDisplay]) }}
                    {{ __('Visit the event page to view the confirmed details and book your tickets when booking is available.') }}
                </p>

                {{-- Key Information Grid --}}
                <div class="event-details-card">
                    <div class="detail-row">
                        <div class="detail-label">{{ __('Date') }}</div>
                        <div class="detail-value">📅 {{ $eventDateStr }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">{{ __('Time') }}</div>
                        <div class="detail-value">⏰ {{ $timeDisplay }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">{{ __('Location') }}</div>
                        <div class="detail-value">📍 {{ $locationDisplay }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">{{ __('Pricing') }}</div>
                        <div class="detail-value">🎟️ {{ $priceDisplay }}</div>
                    </div>
                    @if($event->vendorProfile)
                        <div class="detail-row">
                            <div class="detail-label">{{ __('Organizer') }}</div>
                            <div class="detail-value">🏢 {{ $event->vendorProfile->business_name }}</div>
                        </div>
                    @endif
                </div>

                {{-- Short Description --}}
                @if($event->description)
                    <div class="description-box">
                        {{ \Illuminate\Support\Str::limit(strip_tags($event->description), 280) }}
                    </div>
                @endif

                {{-- Call To Action --}}
                <div class="actions-box">
                    @if($isBookingAvailable)
                        <a href="{{ $eventUrl }}#tickets" class="btn-primary" style="color: #ffffff;">
                            🎟️ {{ __('Book Your Tickets Now') }}
                        </a>
                        <br>
                        <a href="{{ $eventUrl }}" class="btn-secondary">
                            {{ __('View Event Details') }}
                        </a>
                    @else
                        <a href="{{ $eventUrl }}" class="btn-primary" style="color: #ffffff;">
                            {{ __('View Event Details') }} &rarr;
                        </a>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div class="footer">
                <p style="margin: 0 0 6px;">
                    {{ __('You received this notification because you clicked "Notify Me" on :app for this event.', ['app' => config('app.name', 'Huddle Events')]) }}
                </p>
                <p style="margin: 0;">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Huddle Events') }}. {{ __('All rights reserved.') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
