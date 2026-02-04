<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class MobileViewSettingsController extends Controller
{
    public function show()
    {
        $settings = Setting::first();

        $resolveLabel = function (?string $primary, ?string $secondary, string $fallback): string {
            $primary = is_string($primary) ? trim($primary) : '';
            if ($primary !== '') {
                return $primary;
            }
            $secondary = is_string($secondary) ? trim($secondary) : '';
            if ($secondary !== '') {
                return $secondary;
            }
            return $fallback;
        };

        return response()->json([
            'views' => [
                'menu' => [
                    'label' => $resolveLabel($settings?->tab_label_menu, $settings?->button_label_menu, 'Menú'),
                    'enabled' => $settings?->show_tab_menu ?? true,
                ],
                'cocktails' => [
                    'label' => $resolveLabel($settings?->tab_label_cocktails, $settings?->button_label_cocktails, 'Cócteles'),
                    'enabled' => $settings?->show_tab_cocktails ?? true,
                ],
                'wines' => [
                    'label' => $resolveLabel($settings?->tab_label_wines, $settings?->button_label_wines, 'Café & Brunch'),
                    'enabled' => $settings?->show_tab_wines ?? true,
                ],
                'cantina' => [
                    'label' => $resolveLabel($settings?->tab_label_cantina, $settings?->button_label_cantina, 'Cantina'),
                    'enabled' => $settings?->show_tab_cantina ?? true,
                ],
            ],
        ]);
    }
}
