<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateTwilioSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) config('services.twilio.token');
        $signature = (string) $request->header('X-Twilio-Signature');

        if ($token === '' || $signature === '' || ! hash_equals($this->expectedSignature($request, $token), $signature)) {
            Log::warning('Twilio signature rejected.', ['path' => $request->path()]);
            abort(403);
        }

        return $next($request);
    }

    private function expectedSignature(Request $request, string $token): string
    {
        // Configured URL, not the request host: behind a proxy the app may see http while Twilio signed https.
        $payload = rtrim((string) config('ivr.public_url'), '/') . $request->getRequestUri();

        foreach ($this->rawParameters($request) as $key => $value) {
            $payload .= $key . $value;
        }

        return base64_encode(hash_hmac('sha1', $payload, $token, true));
    }

    /**
     * Reads the raw body because Laravel's global middleware trims and nulls input values,
     * which would change what Twilio signed.
     */
    private function rawParameters(Request $request): array
    {
        $parameters = [];

        foreach (array_filter(explode('&', (string) $request->getContent()), 'strlen') as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $parameters[urldecode($key)] = urldecode($value);
        }

        ksort($parameters, SORT_STRING);

        return $parameters;
    }
}
