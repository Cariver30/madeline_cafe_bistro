<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class TipSettingsController extends Controller
{
    public function show(): JsonResponse
    {
        $settings = Setting::first();

        $presets = $settings?->tip_presets;
        if (!is_array($presets) || empty($presets)) {
            $presets = [15, 18, 20];
        }

        $presets = array_values(array_unique(array_filter(array_map(function ($value) {
            if (!is_numeric($value)) {
                return null;
            }
            $number = (float) $value;
            if ($number <= 0 || $number > 100) {
                return null;
            }
            return round($number, 2);
        }, $presets))));

        if (empty($presets)) {
            $presets = [15, 18, 20];
        }

        return response()->json([
            'presets' => $presets,
            'allow_custom' => (bool) ($settings?->tip_allow_custom ?? true),
            'allow_skip' => (bool) ($settings?->tip_allow_skip ?? false),
        ]);
    }
}
