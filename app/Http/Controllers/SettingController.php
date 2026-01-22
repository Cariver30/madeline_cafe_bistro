<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Show the settings form.
     */
    public function edit()
    {
        $settings = Setting::first();

        if (! $settings) {
            $settings = Setting::create([]);
        }

        return view('admin.settings.edit', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $settings = Setting::firstOrCreate([]);

        $validated = $request->validate([
            'background_image_cover' => 'nullable|image',
            'background_image_menu' => 'nullable|image',
            'background_image_cocktails' => 'nullable|image',
            'background_image_wines' => 'nullable|image',
            'logo' => 'nullable|image',
            'text_color_cover' => 'nullable|string',
            'text_color_menu' => 'nullable|string',
            'text_color_cocktails' => 'nullable|string',
            'text_color_wines' => 'nullable|string',
            'card_opacity_cover' => 'nullable|numeric|between:0,1',
            'card_opacity_menu' => 'nullable|numeric|between:0,1',
            'card_opacity_cocktails' => 'nullable|numeric|between:0,1',
            'card_opacity_wines' => 'nullable|numeric|between:0,1',
            'font_family_cover' => 'nullable|string',
            'font_family_menu' => 'nullable|string',
            'font_family_cocktails' => 'nullable|string',
            'font_family_wines' => 'nullable|string',
            'button_color_cover' => 'nullable|string',
            'button_color_menu' => 'nullable|string',
            'button_color_cocktails' => 'nullable|string',
            'button_color_wines' => 'nullable|string',
            'category_name_bg_color_menu' => 'nullable|string',
            'category_name_text_color_menu' => 'nullable|string',
            'category_name_font_size_menu' => 'nullable|integer',
            'category_name_bg_color_cocktails' => 'nullable|string',
            'category_name_text_color_cocktails' => 'nullable|string',
            'category_name_font_size_cocktails' => 'nullable|integer',
            'category_name_bg_color_wines' => 'nullable|string',
            'category_name_text_color_wines' => 'nullable|string',
            'category_name_font_size_wines' => 'nullable|integer',
            'card_bg_color_menu' => 'nullable|string',
            'card_bg_color_cocktails' => 'nullable|string',
            'card_bg_color_wines' => 'nullable|string',
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'phone_number' => 'nullable|string',
            'business_hours' => 'nullable|string',
            'button_font_size_cover' => 'nullable|integer',
            'fixed_bottom_font_size' => 'nullable|integer',
            'fixed_bottom_font_color' => 'nullable|string',
        ]);

        $fileFields = [
            'background_image_cover' => 'background_images',
            'background_image_menu' => 'background_images',
            'background_image_cocktails' => 'background_images',
            'background_image_wines' => 'background_images',
            'logo' => 'logos',
        ];

        foreach ($fileFields as $field => $directory) {
            if ($request->hasFile($field)) {
                if ($settings->{$field}) {
                    Storage::disk('public')->delete($settings->{$field});
                }

                $validated[$field] = $request->file($field)->store($directory, 'public');
            }
        }

        $settings->update($validated);

        return redirect()->route('settings.edit')->with('success', 'Configuraciones actualizadas con éxito.');
    }
}
