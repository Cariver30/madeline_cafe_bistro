<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TwilioVoiceTest extends TestCase
{
    private const BASE = 'https://example.test';
    private const TOKEN = 'test-token';
    private const RESTAURANT = '+17877631285';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'ivr.public_url' => self::BASE,
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'services.twilio.sid' => 'ACtest',
            'services.twilio.token' => self::TOKEN,
            'services.twilio.from' => '+17879060988',
            'ivr.transfer_to' => self::RESTAURANT,
            'ivr.business_name' => 'Madeline Cafe Bistro',
        ]);

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->nullable();
            $table->text('business_hours')->nullable();
            $table->timestamps();
        });

        (require database_path('migrations/2026_11_05_120000_add_ivr_controls_to_settings_table.php'))->up();
    }

    private function twilioPost(string $path, array $params = [], ?string $signature = null): TestResponse
    {
        $params += ['From' => '+17875551234', 'To' => '+17873300524'];
        $url = self::BASE . $path;
        $payload = $url;
        $sorted = $params;
        ksort($sorted, SORT_STRING);
        foreach ($sorted as $key => $value) {
            $payload .= $key . $value;
        }
        $signature ??= base64_encode(hash_hmac('sha1', $payload, self::TOKEN, true));

        return $this->call('POST', $url, $params, [], [], ['HTTP_X_TWILIO_SIGNATURE' => $signature], http_build_query($params));
    }

    private function fakeSms(): void
    {
        Http::fake(['api.twilio.com/*' => Http::response(['sid' => 'SM1'], 201)]);
    }

    public function test_rejects_requests_with_a_bad_signature(): void
    {
        $this->twilioPost('/api/twilio/voice/incoming', [], 'invalid')->assertForbidden();
    }

    public function test_rejects_requests_without_a_signature(): void
    {
        $this->post(self::BASE . '/api/twilio/voice/incoming')->assertForbidden();
    }

    public function test_signature_covers_empty_and_padded_values(): void
    {
        $this->twilioPost('/api/twilio/voice/incoming', ['CallerCity' => '', 'CallerName' => ' Ana '])->assertOk();
    }

    public function test_incoming_offers_both_languages(): void
    {
        $this->twilioPost('/api/twilio/voice/incoming')
            ->assertOk()
            ->assertSee('Para español oprima 1', false)
            ->assertSee('For English press 2', false);
    }

    public function test_language_choice_routes_to_the_right_menu(): void
    {
        $this->twilioPost('/api/twilio/voice/language', ['Digits' => '1'])->assertSee('lang=es', false);
        $this->twilioPost('/api/twilio/voice/language', ['Digits' => '2'])->assertSee('lang=en', false);
    }

    public function test_menus_read_all_five_options(): void
    {
        $this->twilioPost('/api/twilio/voice/menu?lang=es&attempt=1')
            ->assertSee('Para ver nuestro horario, pulsa 1', false)
            ->assertSee('Para reservar nuestra sala de actividades, pulsa 4', false)
            ->assertSee('Para hablar con nuestro personal, pulsa 5', false);
        $this->twilioPost('/api/twilio/voice/menu?lang=en&attempt=1')
            ->assertSee('For our menu, press 2', false)
            ->assertSee('To speak with our staff, press 5', false);
    }

    public function test_digit_one_texts_default_hours_from_the_sms_number(): void
    {
        $this->fakeSms();

        $this->twilioPost('/api/twilio/voice/option?lang=es', ['Digits' => '1'])->assertSee('Su mensaje fue enviado', false);

        Http::assertSent(fn (HttpRequest $request) => $request['From'] === '+17879060988'
            && $request['To'] === '+17875551234'
            && str_contains($request['Body'], 'Miércoles 7:00AM A 5:00PM'));
    }

    public function test_saved_hours_replace_the_default(): void
    {
        $this->fakeSms();
        Setting::forceCreate(['ivr_hours_en' => 'Open daily']);

        $this->twilioPost('/api/twilio/voice/option?lang=en', ['Digits' => '1'])->assertOk();

        Http::assertSent(fn (HttpRequest $request) => $request['Body'] === 'Open daily');
    }

    public function test_digit_two_sends_the_menu_and_three_the_location(): void
    {
        $this->fakeSms();

        $this->twilioPost('/api/twilio/voice/option?lang=es', ['Digits' => '2'])->assertOk();
        $this->twilioPost('/api/twilio/voice/option?lang=en', ['Digits' => '3'])->assertOk();

        Http::assertSent(fn (HttpRequest $request) => str_contains($request['Body'], self::BASE . '/menu'));
        Http::assertSent(fn (HttpRequest $request) => str_contains($request['Body'], 'maps.app.goo.gl'));
    }

    public function test_digit_four_sends_the_order_link(): void
    {
        $this->fakeSms();

        $this->twilioPost('/api/twilio/voice/option?lang=es', ['Digits' => '4'])->assertOk();

        Http::assertSent(fn (HttpRequest $request) => str_contains($request['Body'], self::BASE . '/ordenar'));
    }

    public function test_digit_five_transfers_to_the_restaurant(): void
    {
        $this->twilioPost('/api/twilio/voice/option?lang=es', ['Digits' => '5'])
            ->assertSee('>' . self::RESTAURANT . '</Dial>', false)
            ->assertSee('timeout="51"', false);
    }

    public function test_transfer_works_when_the_restaurant_number_is_also_the_public_phone(): void
    {
        Setting::forceCreate(['phone_number' => '(787) 763-1285']);

        $this->twilioPost('/api/twilio/voice/option?lang=es', ['Digits' => '5'])
            ->assertSee('>' . self::RESTAURANT . '</Dial>', false);
    }

    public function test_saved_transfer_number_is_used(): void
    {
        Setting::forceCreate(['ivr_transfer_number' => '+17875550199']);

        $this->twilioPost('/api/twilio/voice/option?lang=es', ['Digits' => '5'])
            ->assertSee('>+17875550199</Dial>', false);
    }

    public function test_does_not_dial_the_line_the_call_was_forwarded_from(): void
    {
        $this->twilioPost('/api/twilio/voice/option?lang=es', ['Digits' => '5', 'ForwardedFrom' => self::RESTAURANT])
            ->assertDontSee('<Dial', false)
            ->assertSee('no podemos atender', false);
    }

    public function test_does_not_dial_the_twilio_number_itself(): void
    {
        config(['ivr.transfer_to' => '+17873300524']);

        $this->twilioPost('/api/twilio/voice/option?lang=es', ['Digits' => '5'])->assertDontSee('<Dial', false);
    }

    public function test_master_toggle_off_skips_the_menu_and_transfers(): void
    {
        Setting::forceCreate(['ivr_enabled' => false, 'ivr_option_transfer' => false]);

        $this->twilioPost('/api/twilio/voice/incoming')
            ->assertDontSee('<Gather', false)
            ->assertSee('>' . self::RESTAURANT . '</Dial>', false);
    }

    public function test_disabled_option_is_not_spoken_and_its_digit_is_rejected(): void
    {
        $this->fakeSms();
        Setting::forceCreate(['ivr_option_menu' => false]);

        $this->twilioPost('/api/twilio/voice/menu?lang=es&attempt=1')
            ->assertDontSee('pulsa 2', false)
            ->assertSee('pulsa 3', false);
        $this->twilioPost('/api/twilio/voice/option?lang=es&attempt=1', ['Digits' => '2'])
            ->assertSee('Opción no válida', false)
            ->assertSee('attempt=2', false);

        Http::assertNothingSent();
    }

    public function test_silent_caller_is_transferred_after_two_attempts(): void
    {
        $this->twilioPost('/api/twilio/voice/menu?lang=es&attempt=3')->assertSee('</Dial>', false);
    }

    public function test_silent_caller_hears_goodbye_when_transfer_is_off(): void
    {
        Setting::forceCreate(['ivr_option_transfer' => false]);

        $this->twilioPost('/api/twilio/voice/menu?lang=en&attempt=3')
            ->assertDontSee('<Dial', false)
            ->assertSee('Thanks for calling', false);
    }

    public function test_failed_sms_tells_the_caller_and_returns_to_the_menu(): void
    {
        Http::fake(['api.twilio.com/*' => Http::response(['message' => 'error'], 400)]);

        $this->twilioPost('/api/twilio/voice/option?lang=es', ['Digits' => '2'])
            ->assertSee('No pudimos enviar', false)
            ->assertSee('<Redirect', false);
    }

    public function test_hidden_caller_id_does_not_attempt_an_sms(): void
    {
        Http::fake();

        $this->twilioPost('/api/twilio/voice/option?lang=es', ['Digits' => '2', 'From' => 'Anonymous'])
            ->assertSee('No pudimos identificar', false);

        Http::assertNothingSent();
    }

    public function test_unanswered_transfer_ends_the_call_in_the_callers_language(): void
    {
        $this->twilioPost('/api/twilio/voice/transfer-result?lang=en', ['DialCallStatus' => 'no-answer'])
            ->assertSee('We cannot take your call', false)
            ->assertSee('<Hangup/>', false);
    }

    public function test_admin_can_save_toggles(): void
    {
        $this->withoutMiddleware()->post(route('admin.ivr-settings.update'), [
            'ivr_enabled' => '0',
            'ivr_option_hours' => '1',
            'ivr_option_menu' => '0',
            'ivr_option_location' => '1',
            'ivr_option_order' => '1',
            'ivr_option_transfer' => '1',
            'ivr_transfer_number' => '+17875550199',
        ])->assertRedirect();

        $settings = Setting::first();
        $this->assertFalse($settings->ivr_enabled);
        $this->assertFalse($settings->ivr_option_menu);
        $this->assertTrue($settings->ivr_option_hours);
        $this->assertSame('+17875550199', $settings->ivr_transfer_number);
    }

    public function test_admin_rejects_a_badly_formatted_transfer_number(): void
    {
        $this->withoutMiddleware()->post(route('admin.ivr-settings.update'), [
            'ivr_enabled' => '1',
            'ivr_option_hours' => '1',
            'ivr_option_menu' => '1',
            'ivr_option_location' => '1',
            'ivr_option_order' => '1',
            'ivr_option_transfer' => '1',
            'ivr_transfer_number' => '787-763-1285',
        ])->assertSessionHasErrors('ivr_transfer_number');
    }

    public function test_admin_form_renders_saved_toggle_state(): void
    {
        $settings = Setting::forceCreate(['ivr_enabled' => false]);

        $html = view('admin.partials.ivr-settings', ['settings' => $settings->fresh()])
            ->withErrors([])
            ->render();

        $this->assertStringContainsString('Teléfono automático (IVR)', $html);
        $this->assertDoesNotMatchRegularExpression('/id="ivr_enabled"[^>]*checked/', $html);
        $this->assertMatchesRegularExpression('/id="ivr_option_menu"[^>]*checked/', $html);
    }
}
