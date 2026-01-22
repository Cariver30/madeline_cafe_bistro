<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'button_label_menu' => 'Menú',
            'button_label_cocktails' => 'Cócteles',
            'button_label_wines' => 'Café & Brunch',
            'button_label_events' => 'Eventos especiales',
            'button_label_vip' => 'Lista VIP',
            'button_label_reservations' => 'Reservas',
            'show_tab_menu' => true,
            'show_tab_cocktails' => true,
            'show_tab_wines' => true,
            'show_tab_events' => true,
            'show_tab_campaigns' => false,
            'show_tab_popups' => true,
            'show_tab_loyalty' => true,
            'show_cta_menu' => true,
            'show_cta_cafe' => true,
            'show_cta_cocktails' => true,
            'show_cta_events' => true,
            'show_cta_reservations' => true,
            'show_cta_vip' => true,
            'featured_card_bg_color' => '#0f172a',
            'featured_card_text_color' => '#ffffff',
            'featured_tab_bg_color' => '#ffffff',
            'featured_tab_text_color' => '#ffffff',
            'cover_cta_vip_bg_color' => '#0f172a',
            'cover_cta_vip_text_color' => '#ffffff',
        ];

        $settings = Setting::first();
        if ($settings) {
            $settings->fill($data)->save();
        } else {
            Setting::create($data);
        }
    }
}
