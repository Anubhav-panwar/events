<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveMultilingualAllPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $vendorUser;
    protected User $customerUser;
    protected Event $event;
    protected VendorProfile $vendorProfile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->adminUser = User::where('role', 'admin')->first() ?: User::factory()->create(['role' => 'admin']);
        $this->vendorUser = User::where('role', 'vendor')->first() ?: User::factory()->create(['role' => 'vendor']);
        $this->customerUser = User::where('role', 'customer')->first() ?: User::factory()->create(['role' => 'customer']);

        $this->event = Event::where('status', 'published')->first() ?: Event::first();
        $this->vendorProfile = VendorProfile::first();
    }

    /**
     * Helper to perform GET request with a specific locale
     */
    protected function getWithLocale(string $uri, string $locale, ?User $user = null)
    {
        $req = $user ? $this->actingAs($user) : $this;
        return $req->withUnencryptedCookie('locale', $locale)
            ->withSession(['locale' => $locale])
            ->get($uri . (str_contains($uri, '?') ? '&' : '?') . 'lang=' . $locale);
    }

    /**
     * 1. Public Marketplace Pages
     */
    public function test_homepage_in_all_three_languages(): void
    {
        // English
        $en = $this->getWithLocale('/', 'en');
        $en->assertStatus(200);
        $en->assertSee('Discover Events.');
        $en->assertSee('Find vendors');

        // German
        $de = $this->getWithLocale('/', 'de');
        $de->assertStatus(200);
        $de->assertSee('Events entdecken.');
        $de->assertSee('Veranstalter finden');

        // Italian
        $it = $this->getWithLocale('/', 'it');
        $it->assertStatus(200);
        $it->assertSee('Scopri eventi.');
        $it->assertSee('Trova organizzatori');
    }

    public function test_events_discovery_and_search_in_all_three_languages(): void
    {
        foreach (['/events', '/search'] as $url) {
            // English
            $en = $this->getWithLocale($url, 'en');
            $en->assertStatus(200);
            $en->assertSee('Target Audience');

            // German
            $de = $this->getWithLocale($url, 'de');
            $de->assertStatus(200);
            $de->assertSee('Zielgruppe');

            // Italian
            $it = $this->getWithLocale($url, 'it');
            $it->assertStatus(200);
            $it->assertSee('Pubblico di destinazione');
        }
    }

    public function test_event_detail_page_in_all_three_languages(): void
    {
        $url = '/e/' . $this->event->slug;

        // English
        $en = $this->getWithLocale($url, 'en');
        $en->assertStatus(200);
        $en->assertSee('Day &amp; Time', false);
        $en->assertSee('Category / Tags', false);

        // German
        $de = $this->getWithLocale($url, 'de');
        $de->assertStatus(200);
        $de->assertSee('Tag &amp; Uhrzeit', false);
        $de->assertSee('Kategorie / Tags', false);

        // Italian
        $it = $this->getWithLocale($url, 'it');
        $it->assertStatus(200);
        $it->assertSee('Giorno &amp; Ora', false);
        $it->assertSee('Categoria / Tag', false);
    }

    public function test_vendors_directory_and_storefront_in_all_three_languages(): void
    {
        // Directory
        $en = $this->getWithLocale('/vendors', 'en');
        $en->assertStatus(200);
        $en->assertSee('Organizer Directory');

        $de = $this->getWithLocale('/vendors', 'de');
        $de->assertStatus(200);
        $de->assertSee('Veranstalter-Verzeichnis');

        $it = $this->getWithLocale('/vendors', 'it');
        $it->assertStatus(200);
        $it->assertSee('Elenco organizzatori');

        // Vendor Storefront
        if ($this->vendorProfile) {
            $storeUrl = '/vendors/' . $this->vendorProfile->slug;
            $enStore = $this->getWithLocale($storeUrl, 'en');
            $enStore->assertStatus(200);
            $enStore->assertSee('About Organizer');

            $deStore = $this->getWithLocale($storeUrl, 'de');
            $deStore->assertStatus(200);
            $deStore->assertSee('Über den Veranstalter');

            $itStore = $this->getWithLocale($storeUrl, 'it');
            $itStore->assertStatus(200);
            $itStore->assertSee("Info sull&#039;organizzatore", false);
        }
    }

    /**
     * 2. Informational Pages
     */
    public function test_informational_pages_in_all_three_languages(): void
    {
        $pages = [
            '/about' => [
                'en' => 'The Huddle Story &amp; Vision',
                'de' => 'Die Huddle-Geschichte &amp; Vision',
                'it' => 'La storia e la visione di Huddle'
            ],
            '/pricing' => [
                'en' => 'Simple plans for growing teams',
                'de' => 'Einfache Pläne für wachsende Teams',
                'it' => 'Piani semplici per team in crescita'
            ],
            '/faq' => [
                'en' => 'Frequently Asked Questions',
                'de' => 'Häufig gestellte Fragen',
                'it' => 'Domande frequenti'
            ],
            '/contact' => [
                'en' => 'Start a Conversation',
                'de' => 'Gespräch beginnen',
                'it' => 'Avvia una conversazione'
            ],
            '/services' => [
                'en' => 'Event Publishing',
                'de' => 'Event-Veröffentlichung',
                'it' => 'Pubblicazione eventi'
            ],
            '/portfolio' => [
                'en' => 'FutureTech Summit',
                'de' => 'FutureTech Summit',
                'it' => 'FutureTech Summit'
            ],
            '/testimonials' => [
                'en' => 'Testimonials',
                'de' => 'Erfahrungsberichte',
                'it' => 'Testimonianze'
            ]
        ];

        foreach ($pages as $url => $strings) {
            $en = $this->getWithLocale($url, 'en');
            $en->assertStatus(200);
            $en->assertSee($strings['en'], false);

            $de = $this->getWithLocale($url, 'de');
            $de->assertStatus(200);
            $de->assertSee($strings['de'], false);

            $it = $this->getWithLocale($url, 'it');
            $it->assertStatus(200);
            $it->assertSee($strings['it'], false);
        }
    }

    /**
     * 3. Auth Pages
     */
    public function test_auth_pages_in_all_three_languages(): void
    {
        $authPages = [
            '/login' => [
                'en' => 'Welcome back',
                'de' => 'Willkommen zurück',
                'it' => 'Bentornato'
            ],
            '/register' => [
                'en' => 'Create your account',
                'de' => 'Erstellen Sie Ihr Konto',
                'it' => 'Crea il tuo account'
            ],
            '/forgot-password' => [
                'en' => 'Email Password Reset Link',
                'de' => 'Link zum Zurücksetzen des Passworts senden',
                'it' => 'Invia link per reimpostare la password'
            ]
        ];

        foreach ($authPages as $url => $strings) {
            $en = $this->getWithLocale($url, 'en');
            $en->assertStatus(200);
            $en->assertSee($strings['en']);

            $de = $this->getWithLocale($url, 'de');
            $de->assertStatus(200);
            $de->assertSee($strings['de']);

            $it = $this->getWithLocale($url, 'it');
            $it->assertStatus(200);
            $it->assertSee($strings['it']);
        }
    }

    /**
     * 4. Customer Account Pages
     */
    public function test_customer_account_pages_in_all_three_languages(): void
    {
        // Saved Events
        $enSaved = $this->getWithLocale('/account/saved', 'en', $this->customerUser);
        $enSaved->assertStatus(200);
        $enSaved->assertSee('Saved Events');

        $deSaved = $this->getWithLocale('/account/saved', 'de', $this->customerUser);
        $deSaved->assertStatus(200);
        $deSaved->assertSee('Gespeicherte Events');

        $itSaved = $this->getWithLocale('/account/saved', 'it', $this->customerUser);
        $itSaved->assertStatus(200);
        $itSaved->assertSee('Eventi salvati');

        // Tickets
        $enTickets = $this->getWithLocale('/account/tickets', 'en', $this->customerUser);
        $enTickets->assertStatus(200);
        $enTickets->assertSee('My Tickets');

        $deTickets = $this->getWithLocale('/account/tickets', 'de', $this->customerUser);
        $deTickets->assertStatus(200);
        $deTickets->assertSee('Meine Tickets');

        $itTickets = $this->getWithLocale('/account/tickets', 'it', $this->customerUser);
        $itTickets->assertStatus(200);
        $itTickets->assertSee('I miei biglietti');
    }

    /**
     * 5. Vendor CRM Pages
     */
    public function test_vendor_crm_pages_in_all_three_languages(): void
    {
        // Dashboard
        $enDash = $this->getWithLocale('/vendor/dashboard', 'en', $this->vendorUser);
        $enDash->assertStatus(200);
        $enDash->assertSee('Vendor Console');

        $deDash = $this->getWithLocale('/vendor/dashboard', 'de', $this->vendorUser);
        $deDash->assertStatus(200);
        $deDash->assertSee('Veranstalter-Konsole');

        $itDash = $this->getWithLocale('/vendor/dashboard', 'it', $this->vendorUser);
        $itDash->assertStatus(200);
        $itDash->assertSee('Console Organizzatore');

        // Event Create Form
        $enCreate = $this->getWithLocale('/vendor/events/create', 'en', $this->vendorUser);
        $enCreate->assertStatus(200);
        $enCreate->assertSee('1. Basic Event Details');

        $deCreate = $this->getWithLocale('/vendor/events/create', 'de', $this->vendorUser);
        $deCreate->assertStatus(200);
        $deCreate->assertSee('1. Grundlegende Event-Details');

        $itCreate = $this->getWithLocale('/vendor/events/create', 'it', $this->vendorUser);
        $itCreate->assertStatus(200);
        $itCreate->assertSee("1. Dettagli di base dell'evento");

        // Profile Edit
        $enProf = $this->getWithLocale('/vendor/profile', 'en', $this->vendorUser);
        $enProf->assertStatus(200);
        $enProf->assertSee('1. Business Overview');

        $deProf = $this->getWithLocale('/vendor/profile', 'de', $this->vendorUser);
        $deProf->assertStatus(200);
        $deProf->assertSee('1. Unternehmensübersicht');

        $itProf = $this->getWithLocale('/vendor/profile', 'it', $this->vendorUser);
        $itProf->assertStatus(200);
        $itProf->assertSee('1. Panoramica aziendale');

        // Checkin
        $enCheck = $this->getWithLocale('/vendor/checkin', 'en', $this->vendorUser);
        $enCheck->assertStatus(200);
        $enCheck->assertSee('Attendee Ticket Check-In');

        $deCheck = $this->getWithLocale('/vendor/checkin', 'de', $this->vendorUser);
        $deCheck->assertStatus(200);
        $deCheck->assertSee('Teilnehmer-Ticket-Check-in');

        $itCheck = $this->getWithLocale('/vendor/checkin', 'it', $this->vendorUser);
        $itCheck->assertStatus(200);
        $itCheck->assertSee('Check-in biglietti partecipanti');
    }

    /**
     * 6. Admin CRM Pages
     */
    public function test_admin_crm_tabs_in_all_three_languages(): void
    {
        $tabs = [
            'overview' => [
                'en' => 'Super Admin Console',
                'de' => 'Super-Admin-Konsole',
                'it' => 'Console Super Admin'
            ],
            'users' => [
                'en' => 'User & Access Management',
                'de' => 'Benutzer- & Zugriffsverwaltung',
                'it' => 'Gestione utenti e accessi'
            ],
            'events' => [
                'en' => 'Platform Events Management',
                'de' => 'Plattform-Eventverwaltung',
                'it' => 'Gestione eventi della piattaforma'
            ],
            'vendors' => [
                'en' => 'Vendors Directory & Moderation',
                'de' => 'Veranstalterverzeichnis & Moderation',
                'it' => 'Elenco organizzatori e moderazione'
            ],
            'financials' => [
                'en' => 'Financial Analytics & Volume',
                'de' => 'Finanzanalysen & Volumen',
                'it' => 'Analisi finanziarie e volumi'
            ]
        ];

        foreach ($tabs as $tab => $strings) {
            $url = '/admin?tab=' . $tab;

            $en = $this->getWithLocale($url, 'en', $this->adminUser);
            $en->assertStatus(200);
            $en->assertSee($strings['en']);

            $de = $this->getWithLocale($url, 'de', $this->adminUser);
            $de->assertStatus(200);
            $de->assertSee($strings['de']);

            $it = $this->getWithLocale($url, 'it', $this->adminUser);
            $it->assertStatus(200);
            $it->assertSee($strings['it']);
        }
    }

    /**
     * 7. Anti-Auto-Translation Meta and Class Protection
     */
    public function test_anti_translation_headers_present(): void
    {
        $res = $this->get('/');
        $res->assertStatus(200);
        $res->assertSee('notranslate');
        $res->assertSee('<meta name="google" content="notranslate">', false);
    }
}
