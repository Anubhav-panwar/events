You are a senior product architect and Laravel engineer.

Design and structure a TripAdvisor-style Event & Vendor Marketplace platform where vendors can promote activities/events and users can discover, save, share, and book tickets.

==============================
1. CORE PRODUCT REQUIREMENTS
==============================

Build the system based on the following modules (A–F):

--------------------------------
A) Vendor Presentation Module
--------------------------------
- Vendors can present their activity/place.
- Vendor Profile Page should include:
  - Vendor name
  - Description
  - Image & video gallery
  - Address with map
  - Opening hours
  - Social media links
  - List of upcoming events by this vendor
  - Optional “Follow Vendor” feature

--------------------------------
B) Vendor Posts / Advertisements (Events)
--------------------------------
- Treat every post/advertisement as an Event Listing.
- Event fields:
  - Title
  - Description
  - Start date & time
  - End date & time
  - Location (venue name, city, address, latitude, longitude)
  - Category (Workshop, Party, Marathon, etc.)
  - “For whom” (age group, interests, audience tags)
  - Media gallery (images/videos)
  - Visibility: Draft / Published
  - Featured flag
- Users can search and browse events using:
  - Keyword search
  - Category filters
  - Date range
  - Price (free / paid)
  - Distance radius (near me)

--------------------------------
C) Save to Calendar
--------------------------------
- Users can save/bookmark events in their account.
- “Add to Calendar” feature using:
  - .ics file download (works on iPhone, Android, Google Calendar, Outlook)
  - Optional future integration: Google Calendar OAuth

--------------------------------
D) Share with Friends / Social Media
--------------------------------
- Share buttons for events:
  - WhatsApp
  - Facebook
  - X (Twitter)
  - LinkedIn
  - Copy link
- Optional referral tracking using URL parameter (?ref=userid)

--------------------------------
E) Ticket Purchase System
--------------------------------
- Events can be:
  - Free (no checkout)
  - Paid
- Paid events support ticket types:
  - General
  - VIP
  - Early Bird
- Ticket configuration:
  - Price
  - Quantity
  - Sales start & end window
- Checkout & payment:
  - Stripe payment gateway
- After successful payment:
  - Generate booking
  - Generate unique ticket QR codes
- Vendor dashboard:
  - Scan & validate tickets using QR code

--------------------------------
F) Location-Based Search
--------------------------------
- Store latitude & longitude for vendors and events.
- Support search by:
  - User’s current location (browser location)
  - Typed city/place
  - Radius filter (5km, 10km, 25km, 50km)

==============================
2. TECH STACK (LARAVEL READY)
==============================

Backend:
- Laravel 10 or 11
- Authentication: Laravel Breeze or Jetstream
- Roles & Permissions: spatie/laravel-permission
- Media uploads (images/videos): spatie/laravel-medialibrary
- Payments: Stripe (stripe-php or Laravel Cashier)
- Search:
  - Basic: MySQL full-text search + filters
  - Advanced: Laravel Scout + Meilisearch

Frontend:
- Blade + Tailwind CSS (preferred for modern SaaS UI)
- OR Blade + Bootstrap if needed

==============================
3. DATABASE DESIGN
==============================

Users & Roles:
- users
- roles, permissions (Spatie)
- Roles: admin, vendor, user

Vendors:
- vendors:
  - id
  - user_id
  - name
  - slug
  - description
  - phone, email
  - address, city, country
  - latitude, longitude
  - website, instagram, facebook
  - opening_hours (JSON)
  - status (active/inactive)

Events:
- events:
  - id
  - vendor_id
  - title
  - slug
  - description
  - category_id
  - start_datetime
  - end_datetime
  - venue_name
  - address, city, country
  - latitude, longitude
  - audience (JSON)
  - is_featured (boolean)
  - is_published (boolean)
  - event_type (free/paid)
  - base_price (nullable)
  - capacity (nullable)
  - created_at

Categories:
- categories:
  - id
  - name
  - slug
  - icon

Media:
- Use Spatie Media Library linked to vendors and events.

Saved Events:
- event_saves:
  - id
  - user_id
  - event_id
  - created_at

Tickets:
- ticket_types:
  - id
  - event_id
  - name
  - price
  - quantity_total
  - quantity_sold
  - sales_start
  - sales_end

Orders:
- orders:
  - id
  - user_id
  - event_id
  - total_amount
  - currency
  - status (pending/paid/failed/refunded)
  - payment_provider (stripe)
  - payment_intent_id
  - created_at

Order Items:
- order_items:
  - id
  - order_id
  - ticket_type_id
  - quantity
  - unit_price

Tickets Issued:
- tickets:
  - id
  - order_item_id
  - ticket_code (unique)
  - qr_data or qr_path
  - attendee_name (optional)
  - checked_in_at (nullable)

==============================
4. REQUIRED PAGES
==============================

Public:
- Home (hero, upcoming events, featured events, blogs)
- /events (all events with filters & location search)
- /events/{slug} (event details, tickets, map, save/share/calendar)
- /vendors/{slug} (vendor profile)
- Blog listing
- Contact page

User:
- /account/saved (saved events)
- /account/tickets (my tickets)

Vendor Dashboard:
- /vendor/dashboard
- Vendor profile edit
- Event list
- Create/Edit event
- Manage ticket types
- QR code check-in & validation

Admin:
- Manage users
- Manage vendors
- Manage events
- Manage categories
- Manage featured events

==============================
5. CONTROLLERS & ROUTES
==============================

- EventController@index (search & filters)
- EventController@show
- VendorController@show
- SaveEventController@store / destroy
- CalendarController@ics (download .ics file)
- VendorEventController (CRUD)
- VendorTicketController (ticket types)
- CheckInController (QR validation)
- CheckoutController@createSession
- CheckoutController@success / cancel
- WebhookController@stripe

==============================
6. KEY IMPLEMENTATIONS
==============================

- Location-based search using Haversine formula
- City geocoding via Google Maps or OpenStreetMap Nominatim
- ICS calendar export
- Stripe checkout + webhook handling
- QR code generation & scanning
- OpenGraph meta tags for social sharing

==============================
7. DELIVERY EXPECTATION
==============================

Generate:
- System architecture
- Laravel folder structure
- Database migrations
- Models & relationships
- Controllers & responsibilities
- Blade page structure
- Vendor dashboard layout
- Step-by-step implementation plan

Focus on clean, scalable, production-ready Laravel code.
