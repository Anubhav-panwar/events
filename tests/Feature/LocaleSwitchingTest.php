<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocaleSwitchingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_default_visit_defaults_to_english(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $this->assertEquals('en', App::getLocale());
        $response->assertSee('Explore Events');
        $response->assertSee('Find Vendors');
    }

    public function test_user_can_switch_to_german_via_get_route(): void
    {
        $response = $this->get('/locale/de');
        $response->assertStatus(302);
        $response->assertSessionHas('locale', 'de');
        $response->assertPlainCookie('locale', 'de');

        // Subsequent request with the cookie should render German content
        $page = $this->withUnencryptedCookie('locale', 'de')->get('/');
        $page->assertStatus(200);
        $page->assertSee('Events entdecken');
        $page->assertSee('Veranstalter finden');
    }

    public function test_user_can_switch_to_italian_via_get_route(): void
    {
        $response = $this->get('/locale/it');
        $response->assertStatus(302);
        $response->assertSessionHas('locale', 'it');
        $response->assertPlainCookie('locale', 'it');

        // Subsequent request with the cookie should render Italian content
        $page = $this->withUnencryptedCookie('locale', 'it')->get('/');
        $page->assertStatus(200);
        $page->assertSee('Esplora eventi');
        $page->assertSee('Trova organizzatori');
    }

    public function test_invalid_locale_falls_back_to_english(): void
    {
        $response = $this->get('/locale/invalid_code');
        $response->assertStatus(302);
        $response->assertSessionHas('locale', 'en');
        $response->assertPlainCookie('locale', 'en');

        $page = $this->withUnencryptedCookie('locale', 'invalid_code')->get('/');
        $page->assertStatus(200);
        $page->assertSee('Explore Events');
    }

    public function test_ajax_post_locale_switch_returns_json_and_sets_cookie(): void
    {
        $response = $this->postJson('/locale/de');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'locale' => 'de',
        ]);
        $response->assertPlainCookie('locale', 'de');
    }

    public function test_query_parameter_lang_overrides_and_persists(): void
    {
        $response = $this->get('/?lang=it');
        $response->assertStatus(200);
        $response->assertSessionHas('locale', 'it');
        $response->assertPlainCookie('locale', 'it');
        $response->assertSee('Esplora eventi');
    }

    public function test_inner_pages_render_in_selected_language(): void
    {
        // About page in German
        $aboutDe = $this->withUnencryptedCookie('locale', 'de')->get('/about');
        $aboutDe->assertStatus(200);
        $aboutDe->assertSee('Über uns');
        $aboutDe->assertSee('Die Huddle-Geschichte &amp; Vision', false);

        // Contact page in Italian
        $contactIt = $this->withUnencryptedCookie('locale', 'it')->get('/contact');
        $contactIt->assertStatus(200);
        $contactIt->assertSee('Contattaci');
        $contactIt->assertSee('Avvia una conversazione');

        // Login page in German
        $loginDe = $this->withUnencryptedCookie('locale', 'de')->get('/login');
        $loginDe->assertStatus(200);
        $loginDe->assertSee('Willkommen zurück');
        $loginDe->assertSee('E-Mail-Adresse');

        // Register page in Italian
        $registerIt = $this->withUnencryptedCookie('locale', 'it')->get('/register');
        $registerIt->assertStatus(200);
        $registerIt->assertSee('Crea il tuo account');
        $registerIt->assertSee('Conferma password');
    }

    public function test_authenticated_user_persists_language_in_account_and_crm(): void
    {
        $user = User::where('role', 'user')->first();

        $savedDe = $this->actingAs($user)
            ->withUnencryptedCookie('locale', 'de')
            ->get('/account/saved');

        $savedDe->assertStatus(200);
        $savedDe->assertSee('Gespeicherte Events');

        $ticketsIt = $this->actingAs($user)
            ->withUnencryptedCookie('locale', 'it')
            ->get('/account/tickets');

        $ticketsIt->assertStatus(200);
        $ticketsIt->assertSee('I miei biglietti');
    }

    public function test_404_error_page_is_localized(): void
    {
        $responseDe = $this->withUnencryptedCookie('locale', 'de')->get('/non-existent-test-route-404');
        $responseDe->assertStatus(404);
        $responseDe->assertSee('Hoppla! Seite nicht gefunden');
        $responseDe->assertSee('Zurück zur Startseite');

        $responseIt = $this->withUnencryptedCookie('locale', 'it')->get('/non-existent-test-route-404');
        $responseIt->assertStatus(404);
        $responseIt->assertSee('Ops! Pagina non trovata');
        $responseIt->assertSee('Torna alla home');
    }

    public function test_event_details_page_renders_completely_in_selected_language(): void
    {
        // Event details in German
        $eventDe = $this->withUnencryptedCookie('locale', 'de')->get('/e/opening-night-demo');
        $eventDe->assertStatus(200);
        $eventDe->assertSee('Eventdetails');
        $eventDe->assertSee('Tag &amp; Uhrzeit', false);
        $eventDe->assertSee('Veranstaltungsort');
        $eventDe->assertSee('Zielgruppe');
        $eventDe->assertSee('Alle herzlich willkommen');
        $eventDe->assertSee('Im Kalender speichern');
        $eventDe->assertSee('Mit Freunden teilen');
        $eventDe->assertSee('Tickets');
        $eventDe->assertSee('Standard-Einlass');
        $eventDe->assertSee('Eröffnungsabend-Demo');
        $eventDe->assertSee('Erleben Sie mit uns einen unvergesslichen Abend.');

        // Event details in Italian
        $eventIt = $this->withUnencryptedCookie('locale', 'it')->get('/e/opening-night-demo');
        $eventIt->assertStatus(200);
        $eventIt->assertSee('Dettagli evento');
        $eventIt->assertSee('Giorno &amp; Ora', false);
        $eventIt->assertSee('Luogo');
        $eventIt->assertSee('Per chi');
        $eventIt->assertSee('Tutti benvenuti');
        $eventIt->assertSee('Salva nel calendario');
        $eventIt->assertSee('Condividi con gli amici');
        $eventIt->assertSee('Biglietti');
        $eventIt->assertSee('Ingresso generale');
        $eventIt->assertSee('Demo della serata inaugurale');
        $eventIt->assertSee('Unisciti a noi per una serata indimenticabile.');
    }
}

