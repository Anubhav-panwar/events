# Event Platform — Client Requirements Verification Audit

**Meeting date:** 17 October 2026  
**Audit date:** 20 October 2026  
**Scope:** Read-only review of repository behavior and automated tests. No source, schema, or data changes were made for this audit. The existing working tree already contains unrelated edits to `tailwind.config.js` and `public/build/*`; these were left untouched.

## Executive summary

| # | Requirement | Status |
|---:|---|---|
| 1 | German and Italian translations | PARTIAL |
| 2 | Event audience categories | PARTIAL |
| 3 | Upcoming events and reminder email | PARTIAL |
| 4 | Normal notifications | PARTIAL |
| 5 | Event recommendations | PASS |
| 6 | Location and Google Maps | PARTIAL |
| 7 | Photo and video upload limits | PARTIAL |
| 8 | Event and vendor reviews | PARTIAL |
| 9 | Google and device calendar | PARTIAL |
| 10 | Event and vendor sharing | PARTIAL |
| 11 | Vendor sales and revenue reporting | FAIL |
| 12 | Paid ticket tiers | PASS |
| 13 | Ticket code and validation | PARTIAL |
| 14 | Admin user blocking | PASS |

**Counts:** 3 PASS, 10 PARTIAL, 1 FAIL, 0 MISSING, 0 NOT VERIFIED. PARTIAL is used where code paths and tests establish meaningful functionality but a requested condition or live integration remains incomplete or unproven. External delivery and production behavior are called out separately.

## Findings by requirement

### 1. German and Italian translations — PARTIAL

- **Evidence/current behavior:** `app/Http/Middleware/SetLocale.php` resolves `en`, `de`, and `it`; locale routes are registered in `routes/web.php`; `resources/views/components/language-switcher.blade.php` persists the selection. JSON interface catalogs exist at `lang/en.json`, `lang/de.json`, and `lang/it.json`, with conventional language files as well. `ComprehensiveMultilingualAllPagesTest` and `LocaleSwitchingTest` exercise public, account, vendor, admin, and event pages across the three locales; all passed in this run.
- **Gaps:** These tests check selected pages and strings, not every key or every validation/email string. Vendor-authored event descriptions/titles are stored content, not translated into German/Italian by this mechanism. Some backend messages are authored directly in English, and actual email rendering in each locale was not comprehensively verified. A static all-keys parity audit was not run.
- **Recommended fix/dependencies:** Add catalog parity and untranslated-string checks; make validation and mail templates use locale-aware translation keys; define whether event content translation is in scope. No external credentials required.
- **Test/result:** `php artisan test` passed 162 tests / 988 assertions, including the multilingual and locale-switching suites. This proves covered test cases only, not complete linguistic quality.

### 2. Event audience categories — PARTIAL

- **Evidence/current behavior:** `Event` stores canonical audience categories as JSON; `EventStoreRequest` normalizes/validates values; vendor create/edit forms expose audience selection; `SearchController` applies backend JSON audience filters. `EventAudienceCategoryTest` covers persistence, editing, single/multiple selection and combined filters; all passed.
- **Gaps:** The UI calls the children category **Kids**, rather than the requested **Children**. Children is accepted as an alias, but canonical category/display wording differs. These are event audience categories and are distinct from ticket tiers.
- **Recommended fix/dependencies:** Confirm client wording and use “Children” in the UI/catalog while preserving existing stored values and aliases. No external dependency.
- **Test/result:** `EventAudienceCategoryTest` passed (10 tests in the full run).

### 3. Upcoming events and reminder email — PARTIAL

- **Evidence/current behavior:** `EventReminderController` stores per-user/event subscriptions; `EventService` calls `EventNotificationService` on the first eligible date confirmation; `VendorEventController` exposes retry for failed notifications. The service sends email synchronously, records sent/failed state, and avoids repeat sends. `UpcomingEventsAndNotifyMeTest` covers subscription lifecycle, TBA booking/calendar restrictions, first announcement, duplicate suppression, incomplete updates, recipient failure isolation, template contents, and retry; all passed.
- **Gaps:** Delivery is not queued: the operation calls the mailer in the request path, so queue failure handling is not applicable and slow/failing SMTP can affect the vendor save. Test mail is faked and does not establish real delivery. A prior SMTP connection attempt reached port 465 but Laravel/Symfony TLS verification failed before authentication, so credentials and delivery remain unverified. Production SMTP, certificates, DNS, inbox placement, and deployed worker/configuration were not inspected. A failure after some sends can leave a mixed recipient state; manual retry is the recovery path.
- **Recommended fix/dependencies:** Verify SMTP certificate chain and credentials in a safe staging environment; then use a queued, idempotent notification job with observable queue health and provider outcomes. Deployment access, working SMTP credentials and a test mailbox/provider are required for delivery proof.
- **Test/result:** Full automated reminder suite passed (17 tests). It uses the testing mailer and cannot verify SMTP delivery.

### 4. Normal notifications — PARTIAL

- **Evidence/current behavior:** Email exists for auth verification/password reset and the special upcoming-event announcement. `User` has Laravel `Notifiable`, but repository search found no app-level `Notification::send`, `->notify()` workflow, standard Laravel `notifications` table/migration, or in-app unread/read feed. Ticket checkout success is shown in the web flow.
- **Gaps:** There is no general persisted in-app notification center, unread count/read state, nor verified order confirmation/receipt email. Reminder email is a separate feature and should not be counted as a general notification system. Recipient and link behavior for missing notification types therefore cannot be audited as implemented.
- **Recommended fix/dependencies:** Agree the notification event/recipient matrix, then implement persisted notifications and/or targeted email templates, with authorization and deduplication tests. No external dependency for in-app persistence; email needs SMTP.
- **Test/result:** Notification-related reminder and auth tests passed, but no tests establish a general in-app notification system.

### 5. Event recommendations — PASS

- **Evidence/current behavior:** `EventRecommendationService::getRecommendations()` queries available future published events, excludes the current event, and ranks candidates with category, tags/type, location, audience, date and price signals plus user activity where available. `EventController` renders the recommendation component; `/e/{slug}/recommendations` exposes the backend endpoint. Empty result handling exists.
- **Gaps:** Ranking is heuristic rather than a learned recommender, which is consistent with the implemented data-driven signals. Production relevance depends on event/profile data quality.
- **Recommended fix/dependencies:** No required fix identified; monitor ranking quality with real usage data. No external dependency.
- **Test/result:** All 13 `EventRecommendationTest` cases passed, including current/unavailable exclusion, matching signals, personalization, endpoint and empty state.

### 6. Location and Google Maps — PARTIAL

- **Evidence/current behavior:** Vendor event create/edit uses `<x-google-map-picker>` with place search and map pin selection; coordinates are retained internally for markers and distance searches, not manually entered as required form fields. Public pages provide map/navigation. Search offers an explicit “Use My Location” action and a place filter, so denial of GPS still permits place-based search. Backend event location logic can geocode address when coordinates are absent. `config/services.php` reads the Maps key from environment.
- **Gaps:** Automated tests establish rendered controls and configuration presence, not live Google API key validity, billing, browser permission behavior, geocoding quota, or production map loads. Location permission is requested only through the explicit geolocation action. Some search paths need stored/geocoded coordinates for radius proximity; place/city filtering remains the fallback.
- **Recommended fix/dependencies:** Keep internal coordinates; remove only any user-facing raw coordinate presentation/manual entry. Verify the deployed key, allowed referrers, enabled Maps/Places APIs and billing in the target environment. Requires Google Maps credentials and deployment/browser verification.
- **Test/result:** `EventLocationGoogleMapsTest` passed all 6 tests; this is not a live Google integration test.

### 7. Photo and video upload limits — PARTIAL

- **Evidence/current behavior:** `app/Http/Requests/EventStoreRequest.php` sets 15 total event media items, 30 MB per image, and 100 MB per video, including existing files on edit; it validates extensions/MIME. Event images are optimized in `EventService`; uploads use Laravel storage. `EventMediaUploadLimitsTest` exercises these application-level bounds and rejection paths; all passed.
- **Gaps:** This audit did not verify vendor-profile upload caps end-to-end, production PHP `upload_max_filesize`/`post_max_size`, web-server request limits, hosting/storage quota, or any video duration/transcoding/thumbnail policy. Application acceptance at 100 MB does not mean the deployed request stack accepts 100 MB.
- **Recommended fix/dependencies:** Before increasing caps, inventory all profile/event forms and validation, then align PHP, web server/proxy, hosting quota and storage limits with explicit duration/processing policy. Deployment/PHP/web-server and storage-provider access are required.
- **Test/result:** `EventMediaUploadLimitsTest` passed 11 tests. It verifies Laravel validation, not hosting limits.

### 8. Event and vendor reviews — PARTIAL

- **Evidence/current behavior:** Separate `EventReview` and `VendorReview` models/routes/controllers persist ratings and comments; public event/vendor pages show averages and review lists. Controllers authenticate, validate ratings, constrain edits/deletes to the author, prevent owners reviewing their own listing, and require an eligible purchase/ticket. Unique review constraints support one review per reviewer/listing with update behavior.
- **Gaps:** No substantive moderation or rate limiting was found. Event eligibility also accepts a matching ticket record, while ticket records are retained after an order refund; check-in/eligibility policy for refunded ticket holders should be explicit. Automated tests do not establish spam resistance or moderation operations.
- **Recommended fix/dependencies:** Decide refund eligibility and add moderation/reporting and submission throttling if required. No external dependency.
- **Test/result:** `EventAndVendorReviewsTest` passed 11 cases covering auth, eligibility, update/delete and display.

### 9. Google Calendar and device calendar — PARTIAL

- **Evidence/current behavior:** `Event::isCalendarEligible()` gates actions; Google, Outlook and Yahoo URL builders are present; `EventCalendarController::ics()` serves a standard `.ics` download. Calendar controls are hidden for unconfirmed/TBA events. This is URL/ICS export, not OAuth or device-calendar API integration.
- **Gaps:** ICS dates are parsed in the application timezone and converted to UTC without an explicit venue timezone; if event time is intended as local venue time, exported times can be wrong. Missing end time defaults to two hours. ICS escaping does not handle all required backslash/newline cases. No live desktop/mobile calendar application was launched for verification.
- **Recommended fix/dependencies:** Define event timezone and export explicit timezone-aware times; fully escape/fold ICS text lines; verify sample files in Apple Calendar and major clients. No OAuth is needed; client/device verification is required.
- **Test/result:** `CalendarAndSharingIntegrationTest` passed 8 cases for links, ICS response and TBA restrictions. They do not validate cross-client time-zone interpretation.

### 10. Event and vendor profile sharing — PARTIAL

- **Evidence/current behavior:** Event and vendor pages include share controls; `ShareController` redirects supported channels and records clicks. Copy-link and browser Web Share API controls are present in the event page and vendor profile. Public page routes resolve through event/vendor slugs, subject to publication/visibility authorization.
- **Gaps:** Web Share is browser/device dependent. Social preview metadata and production URL behavior depend on page metadata and deployed `APP_URL`; this audit did not verify the public production/staging host or scrape previews from social networks. No external social preview fetch was performed.
- **Recommended fix/dependencies:** Verify canonical/OG title, description, image and URL on deployed public pages, and check drafts/private listings remain inaccessible via direct/share routes. Production base URL and external preview debuggers/network access required.
- **Test/result:** `CalendarAndSharingIntegrationTest` passed 8 tests, including channel redirects and public target routes. This does not prove external preview rendering.

### 11. Vendor ticket sales and revenue reporting — FAIL

- **Evidence/current behavior:** Vendor-only `/vendor/analytics` and `/vendor/analytics/export` routes use `VendorAnalyticsService`; event ownership scopes the dataset. Metrics derive ticket quantities from order items and revenue from order totals; category and date/event filters and CSV export exist. Tests cover scope, calculations and export.
- **Defects:** `computeOverallMetrics()` and `computeEventSummaries()` sum gross only from `status=paid`, then subtract refund amounts only from orders whose status is `refunded`. `CheckoutService::refund()` changes the whole order to `refunded` even for a partial amount. Thus a partial refund removes the order’s entire gross from paid revenue, while refunded amount is only partial; per-order display separately calculates paid-order net. Refunded ticket count counts every item in the order, regardless of partial quantity/amount. This produces inconsistent revenue and ticket totals. Currency is reported as configured `events.currency` while ticket types/orders can carry per-tier currency; no conversion/grouping was established. The implemented refund method changes local order state; payment provider reconciliation/webhook coverage for refunds was not established.
- **Recommended fix/dependencies:** Define refund semantics (partial amount and quantity), reconcile provider refunds, and calculate gross/refunds/net and ticket counts from consistent payment/refund/ticket records. Group totals by currency or convert with a documented rate/source. Add fixtures for partial refunds, mixed currencies, pending orders and reconciliation. Provider credentials/webhooks and financial product decisions are dependencies.
- **Test/result:** `VendorAnalyticsAndReportingTest` passed 9 tests, but current tests miss the partial refund and mixed-currency cases. Passing tests do not clear the code-level accounting inconsistency.

### 12. Paid ticket tiers — PASS

- **Evidence/current behavior:** Vendor ticket tier create/edit/deactivate flows support Bronze, Silver, Gold, Diamond and custom names, with independent price, currency, inventory, sales windows and active state. Checkout validates the selected tier and availability/inventory; order items store tier and unit price; paid order ticket issuance generates ticket records/codes. Analytics includes category breakdown. `PaidTicketCategoriesAndVerificationTest` exercises tier CRUD, sold-out/inactive safety, purchase/issuance and verification.
- **Gaps:** Default tier labels and suggested prices are preset, but the tiers are configurable; custom naming/pricing is supported. Live payment-provider settlement is outside the test suite.
- **Recommended fix/dependencies:** No tier capability gap found. Separately resolve reporting refund/currency defects under requirement 11. Paid production transactions require configured payment credentials, but were not used in this audit.
- **Test/result:** All 9 `PaidTicketCategoriesAndVerificationTest` cases passed as part of the full suite.

### 13. Ticket code and validation — PARTIAL

- **Evidence/current behavior:** `TicketService`/`Ticket::generateUniqueCode()` create a unique human-readable code stored on ticket records; account and vendor views expose ticket details. Vendor routes provide verify/check-in actions; validation checks order payment state, event/vendor ownership and prior check-in. Tests verify code issuance after paid status, rejected refunded/unpaid orders and anti-reuse behavior. A QR image is also generated from ticket data and represents/scans the issued ticket; it is a machine-readable carrier, distinct from the human-entered code.
- **Gaps:** Check-in checks `checked_in_at` and then updates it without a transaction/row lock or conditional atomic update, allowing simultaneous scans to race. The QR image URL uses the third-party `api.qrserver.com` service with encoded ticket payload, disclosing that payload to the provider when fetched. Audit did not confirm which exact QR payload fields are included in every client path. The client phrase “ticket code only” remains ambiguous; present system includes both code and QR.
- **Recommended fix/dependencies:** Make check-in a single atomic state transition; generate QR locally or encode only an opaque ticket code, and document contents/privacy; ask client whether they want code-only display or validation behavior change before altering the UX. No external credential; QR provider is external today.
- **Test/result:** `PaidTicketCategoriesAndVerificationTest` passed 9 cases, including anti-reuse sequentially; concurrency and QR-provider privacy were not tested.

### 14. Admin user blocking — PASS

- **Evidence/current behavior:** Admin UI and routes provide block, unblock and history (`admin.users.block`, `admin.users.unblock`, `admin.users.block_history`) under admin authorization. `AdminUserController` records reason/actor and changes status; `EnsureUserNotBlocked` invalidates active web sessions and rejects protected requests. Login denies blocked accounts. Tests cover authorization, block/unblock, audit history, existing sessions, purchase/review/reminder restrictions and preservation of existing bookings.
- **Gaps:** Session termination for already authenticated requests is enforced on the next middleware-protected request, rather than remotely invalidating a session at the instant of the action. No separate API/token authentication surface was found in this audit; if one is deployed separately, verify its middleware too.
- **Recommended fix/dependencies:** Keep block middleware on every protected web/API surface; verify any separately deployed API or long-lived token clients. No external dependency identified.
- **Test/result:** `AdminUserBlockingAndAccountManagementTest` passed all 11 cases, including blocked login and existing-session denial.

## High-priority requirements

### Reminder email delivery

The code-level workflow is implemented and its idempotency, failure tracking and retry paths pass the mail-faked tests. **Real delivery is not verified.** The previous safe SMTP transport check failed TLS certificate verification before authentication; do not treat the SMTP credentials as validated. The send currently occurs synchronously in the vendor update path. First validate certificate trust and credentials in staging, then establish queue/worker monitoring and provider delivery evidence.

### Vendor ticket sales and revenue reporting

The dashboard, scoped queries, filters and export exist, but reporting is **FAIL** because partial refund/order status semantics make aggregate totals and ticket counts inconsistent. Currency aggregation is also not shown to convert or group by currency. Reconcile against payment/refund records and add partial-refund and mixed-currency tests before relying on totals.

### Ticket category pricing and ticket code validation

Paid tier setup and purchase flow are **PASS** in automated tests. Ticket codes and sequential duplicate-use prevention work; simultaneous check-in is not atomic and QR generation calls an external service with ticket payload data. Preserve tier behavior while hardening atomic redemption and QR privacy. Clarify “ticket code only” with the client.

### Google Maps and location behavior

Map UI and backend location storage/search paths pass repository tests. Those tests do not verify Google credentials, billing, enabled APIs, browser permissions or production map loads. Keep internal coordinates for map pins and distance search; verify configuration in the deployed environment and hide raw coordinates from ordinary user-facing UI.

### Admin user blocking

Admin enforcement, audit trail, login denial and next-request session blocking pass tests. Confirm that any separately deployed API/token surfaces also run the blocked-user middleware; no such separate surface was evidenced in the inspected routes.

## Critical gaps

1. **Revenue reports misstate partial refunds and refunded ticket quantities**; currency totals may combine unlike currencies.
2. **Real reminder email delivery is unverified**, with a prior TLS trust failure before SMTP authentication; sends are synchronous.
3. **Ticket check-in can race** because duplicate-use check and write are not atomic.
4. **Calendar exports lack explicit venue timezone handling** and have incomplete ICS escaping; time accuracy across zones is unproven.
5. **Google Maps, media hosting limits, and public social previews are not verified against production configuration.**

## External dependencies and access needed

- Working SMTP host/account credentials, valid TLS certificate chain, DNS and a non-customer test mailbox; production app/worker/log access to prove delivery.
- Google Maps JavaScript/Places/geocoding APIs, valid key restrictions and billing in the target deployment.
- Deployment PHP/web-server/proxy body-size settings and storage quota/provider limits for uploads.
- Production `APP_URL`, public access and social preview inspection for canonical share URLs/OG tags.
- Payment processor test credentials and webhook access to reconcile payment/refund state and revenue.
- Apple Calendar/Google Calendar/Outlook desktop/mobile clients for end-user ICS compatibility checks.

## Recommended implementation order

1. Correct and reconcile refund, currency and ticket-count reporting; expand tests around partial refunds, pending orders and multiple currencies.
2. Validate SMTP TLS/credentials in staging; move reminder delivery to monitored queued jobs with idempotent retry and delivery observability.
3. Make ticket check-in atomic and remove third-party exposure of ticket payloads in QR generation; confirm client meaning of “ticket code only.”
4. Specify event timezone and correct/validate ICS generation in actual calendar clients.
5. Verify deployed Maps, upload stack limits and share metadata; apply the “Children” label and close translation coverage gaps.
6. Define the standard notification catalog and implement only the agreed in-app/email types.

## Proposed implementation checklist

### Phase A — Financial correctness

- [ ] Decide order-level and ticket-level partial-refund semantics.
- [ ] Reconcile payment-provider refunds with order/refund records.
- [ ] Rebuild report totals with explicit paid, pending, completed, cancelled and refunded definitions.
- [ ] Group or convert currencies; state the reporting currency and conversion source.
- [ ] Add tests for partial refunds, multiple ticket quantities, mixed currencies and export reconciliation.

### Phase B — Email reliability

- [ ] Repair/verify TLS trust and SMTP authentication in staging.
- [ ] Send a controlled test message and capture provider acceptance/delivery evidence.
- [ ] Queue announcement jobs, preserve per-recipient idempotency, and expose retries/failures.
- [ ] Verify deployed worker, scheduler (if used), logs and alerting.

### Phase C — Ticket integrity and privacy

- [ ] Implement an atomic check-in transition and concurrent-scan test.
- [ ] Inspect exact QR payload; generate locally or encode only an opaque code.
- [ ] Confirm the intended meaning of “ticket code only” before changing presentation.

### Phase D — Calendar correctness

- [ ] Define event timezone storage/input behavior.
- [ ] Generate timezone-correct ICS timestamps and standards-compliant escaped/folded fields.
- [ ] Verify Google, Apple and Outlook behavior on desktop and mobile, including DST.

### Phase E — Deployment and client-facing polish

- [ ] Verify Maps APIs/key restrictions/billing and GPS denial fallback on deployed site.
- [ ] Align PHP, web server/proxy and storage quotas with upload policy; document vendor media caps and video duration.
- [ ] Verify production share URL, OG metadata and private/draft access rules.
- [ ] Confirm “Children” copy and translation/catalog coverage, including validation and email text.

### Phase F — Notification scope

- [ ] Agree notification events, recipients, channels, read state and retention with the client.
- [ ] Implement and test the agreed in-app/email notifications with duplicate and authorization checks.

## Verification performed and limits

- Ran `php artisan test`: **162 passed, 988 assertions**, duration 44.22 seconds. PHPUnit config uses SQLite `:memory:`, array cache/session/mail, and sync queue, so this run did not intentionally exercise production mail or payment services.
- Inspected route/controller/service/model/view/migration/test paths for all 14 requirements, including the vendor reports, reminder flow, map picker, reviews, calendar, sharing, ticket checkout/check-in, and admin block enforcement.
- Did not send real emails, charge payments, block real users, alter production records, run migrations, or change source for this audit.
- Automated test success is not evidence of live SMTP, Maps, payment-provider, storage-hosting, social-preview or calendar-client integration. Those remain environment-dependent verification items above.
