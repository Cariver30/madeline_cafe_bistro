<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TapToPayController extends Controller
{
    public function config(Request $request): JsonResponse
    {
        $tpn = config('services.tap_to_pay.tpn');
        $merchantCode = config('services.tap_to_pay.merchant_code');
        $authToken = config('services.tap_to_pay.auth_token');
        $environment = config('services.tap_to_pay.environment', 'UAT');

        if (! $tpn || ! $merchantCode) {
            return response()->json([
                'message' => 'Tap to Pay no esta configurado.',
            ], 503);
        }

        return response()->json([
            'tpn' => $tpn,
            'merchant_code' => $merchantCode,
            'auth_token' => $authToken,
            'environment' => $environment,
        ]);
    }
}
