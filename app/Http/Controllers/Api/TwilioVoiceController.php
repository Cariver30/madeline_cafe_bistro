<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\TwilioSmsClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class TwilioVoiceController extends Controller
{
    private const MAX_MENU_ATTEMPTS = 2;
    private const DIAL_TIMEOUT_SECONDS = 51;

    private const OPTION_TOGGLES = [
        '1' => 'ivr_option_hours',
        '2' => 'ivr_option_menu',
        '3' => 'ivr_option_location',
        '4' => 'ivr_option_order',
        '5' => 'ivr_option_transfer',
    ];

    private const TEXT = [
        'es' => [
            'voice' => 'Polly.Lupe-Neural',
            'language' => 'es-US',
            'intro' => 'Escucha nuestras opciones. Te enviaremos un enlace con la información solicitada.',
            'options' => [
                '1' => 'Para ver nuestro horario, pulsa 1.',
                '2' => 'Para ver nuestro menú, pulsa 2.',
                '3' => 'Para ver nuestra ubicación, pulsa 3.',
                '4' => 'Para reservar nuestra sala de actividades, pulsa 4.',
                '5' => 'Para hablar con nuestro personal, pulsa 5.',
            ],
            'menu_sms' => 'Gracias por su visita.',
            'location_sms' => '¡Esta es nuestra ubicación!',
            'order_sms' => 'Este es el enlace para ordenar en línea',
            'sent' => '¡Gracias! Su mensaje fue enviado. Hasta luego.',
            'invalid' => 'Opción no válida.',
            'no_caller' => 'No pudimos identificar su número para enviarle el texto.',
            'sms_failed' => 'No pudimos enviar el mensaje de texto.',
            'connecting' => 'Un momento, le comunicamos.',
            'unavailable' => 'En este momento no podemos atender su llamada. Por favor intente más tarde.',
            'goodbye' => 'Gracias por llamar. Hasta luego.',
        ],
        'en' => [
            'voice' => 'Polly.Joanna-Neural',
            'language' => 'en-US',
            'intro' => 'Listen to our options. We will text you a link with the requested information.',
            'options' => [
                '1' => 'For our schedule, press 1.',
                '2' => 'For our menu, press 2.',
                '3' => 'For our location, press 3.',
                '4' => 'For online order, press 4.',
                '5' => 'To speak with our staff, press 5.',
            ],
            'menu_sms' => 'Enjoy our menu!',
            'location_sms' => 'Here is our Google location!',
            'order_sms' => 'Here is the link to order online',
            'sent' => 'Your message was sent. Thanks for calling. Bye.',
            'invalid' => 'That is not a valid option.',
            'no_caller' => 'We could not identify your number to send the text.',
            'sms_failed' => 'We could not send the text message.',
            'connecting' => 'One moment, connecting your call.',
            'unavailable' => 'We cannot take your call right now. Please try again later.',
            'goodbye' => 'Thanks for calling. Bye.',
        ],
    ];

    public function __construct(private TwilioSmsClient $sms)
    {
    }

    public function incoming(Request $request): Response
    {
        if (! $this->isOn('ivr_enabled')) {
            return $this->transfer($request, 'es');
        }

        $name = (string) config('ivr.business_name');
        $languageUrl = $this->escape('/api/twilio/voice/language');
        $defaultMenuUrl = $this->escape($this->menuUrl('es', 1));

        return $this->twiml(
            "<Gather numDigits=\"1\" timeout=\"4\" action=\"{$languageUrl}\" method=\"POST\">"
            . $this->say('es', "Gracias por llamar a {$name}. Para español oprima 1.")
            . $this->say('en', 'For English press 2.')
            . '</Gather>'
            . "<Redirect method=\"POST\">{$defaultMenuUrl}</Redirect>"
        );
    }

    public function language(Request $request): Response
    {
        $language = (string) $request->input('Digits') === '2' ? 'en' : 'es';
        $url = $this->escape($this->menuUrl($language, 1));

        return $this->twiml("<Redirect method=\"POST\">{$url}</Redirect>");
    }

    public function menu(Request $request): Response
    {
        $language = $this->requestLanguage($request);
        $attempt = $this->attempt($request);
        $available = array_filter(self::OPTION_TOGGLES, $this->isOn(...));

        if ($attempt > self::MAX_MENU_ATTEMPTS || $available === []) {
            return $this->isOn('ivr_option_transfer')
                ? $this->transfer($request, $language)
                : $this->twiml($this->say($language, self::TEXT[$language]['goodbye']) . '<Hangup/>');
        }

        $spoken = self::TEXT[$language]['intro'] . ' '
            . implode(' ', array_intersect_key(self::TEXT[$language]['options'], $available));
        $optionUrl = $this->escape("/api/twilio/voice/option?lang={$language}&attempt={$attempt}");
        $retryUrl = $this->escape($this->menuUrl($language, $attempt + 1));

        return $this->twiml(
            "<Gather numDigits=\"1\" timeout=\"5\" action=\"{$optionUrl}\" method=\"POST\">"
            . $this->say($language, $spoken)
            . '</Gather>'
            . "<Redirect method=\"POST\">{$retryUrl}</Redirect>"
        );
    }

    public function option(Request $request): Response
    {
        $language = $this->requestLanguage($request);
        $digit = (string) $request->input('Digits');
        $toggle = self::OPTION_TOGGLES[$digit] ?? null;

        if (! $toggle || ! $this->isOn($toggle)) {
            return $this->twiml($this->say($language, self::TEXT[$language]['invalid']) . $this->retryRedirect($request, $language));
        }

        $text = self::TEXT[$language];

        return match ($digit) {
            '1' => $this->sendText($request, $language, $this->hours($language)),
            '2' => $this->sendText($request, $language, $text['menu_sms'] . "\n" . $this->publicUrl(route('menu', [], false))),
            '3' => $this->sendText($request, $language, $text['location_sms'] . "\n" . config('ivr.location_url')),
            '4' => $this->sendText($request, $language, $text['order_sms'] . "\n" . $this->publicUrl(route('online.order.show', [], false))),
            '5' => $this->transfer($request, $language),
        };
    }

    public function transferResult(Request $request): Response
    {
        if (in_array($request->input('DialCallStatus'), ['completed', 'answered'], true)) {
            return $this->twiml('<Hangup/>');
        }

        $language = $this->requestLanguage($request);

        return $this->twiml($this->say($language, self::TEXT[$language]['unavailable']) . '<Hangup/>');
    }

    private function transfer(Request $request, string $language): Response
    {
        $target = trim((string) (Setting::first()?->ivr_transfer_number ?: config('ivr.transfer_to')));

        if ($target === '' || $this->wouldLoop($target, $request)) {
            Log::warning('Twilio IVR transfer unavailable: missing target or it points back to this call.');

            return $this->twiml($this->say($language, self::TEXT[$language]['unavailable']) . '<Hangup/>');
        }

        $number = $this->escape($target);
        $resultUrl = $this->escape("/api/twilio/voice/transfer-result?lang={$language}");
        $timeout = self::DIAL_TIMEOUT_SECONDS;

        return $this->twiml(
            $this->say($language, self::TEXT[$language]['connecting'])
            . "<Dial timeout=\"{$timeout}\" action=\"{$resultUrl}\" method=\"POST\">{$number}</Dial>"
        );
    }

    /**
     * Dialing the line this call arrived on would send the caller straight back into the IVR.
     */
    private function wouldLoop(string $target, Request $request): bool
    {
        $incomingLines = [(string) $request->input('To'), (string) $request->input('ForwardedFrom')];

        return in_array($this->lastTenDigits($target), array_map($this->lastTenDigits(...), $incomingLines), true);
    }

    private function lastTenDigits(string $phone): string
    {
        return substr((string) preg_replace('/\D+/', '', $phone), -10);
    }

    private function sendText(Request $request, string $language, string $body): Response
    {
        $text = self::TEXT[$language];
        $caller = (string) $request->input('From');

        if (! preg_match('/^\+[1-9]\d{9,14}$/', $caller)) {
            return $this->twiml($this->say($language, $text['no_caller']) . $this->retryRedirect($request, $language));
        }

        try {
            $sent = $this->sms->send($caller, $body);
        } catch (Throwable $exception) {
            Log::warning('Twilio IVR SMS failed.', ['error' => $exception->getMessage()]);
            $sent = false;
        }

        if (! $sent) {
            return $this->twiml($this->say($language, $text['sms_failed']) . $this->retryRedirect($request, $language));
        }

        return $this->twiml($this->say($language, $text['sent']) . '<Hangup/>');
    }

    private function hours(string $language): string
    {
        $saved = trim((string) Setting::first()?->{"ivr_hours_{$language}"});

        return $saved !== '' ? $saved : (string) config("ivr.hours.{$language}");
    }

    /**
     * Toggles default to on so a missing settings row or unmigrated column keeps the IVR working.
     */
    private function isOn(string $toggle): bool
    {
        return (bool) (Setting::first()?->{$toggle} ?? true);
    }

    private function requestLanguage(Request $request): string
    {
        return $request->query('lang') === 'en' ? 'en' : 'es';
    }

    private function attempt(Request $request): int
    {
        return max(1, (int) $request->query('attempt', 1));
    }

    private function menuUrl(string $language, int $attempt): string
    {
        return "/api/twilio/voice/menu?lang={$language}&attempt={$attempt}";
    }

    private function retryRedirect(Request $request, string $language): string
    {
        $url = $this->escape($this->menuUrl($language, $this->attempt($request) + 1));

        return "<Redirect method=\"POST\">{$url}</Redirect>";
    }

    private function publicUrl(string $path): string
    {
        return rtrim((string) config('ivr.public_url'), '/') . $path;
    }

    private function say(string $language, string $text): string
    {
        $voice = self::TEXT[$language]['voice'];
        $locale = self::TEXT[$language]['language'];

        return "<Say voice=\"{$voice}\" language=\"{$locale}\">" . $this->escape($text) . '</Say>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function twiml(string $body): Response
    {
        return response("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response>{$body}</Response>", 200)
            ->header('Content-Type', 'text/xml');
    }
}
