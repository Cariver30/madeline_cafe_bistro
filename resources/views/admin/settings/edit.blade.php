<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Edit Settings</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $cocktailLabelAdmin = $settings->tab_label_cocktails ?? 'Cocktails';
    @endphp
    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Background Images -->
        <div class="mb-3">
            <label for="background_image_cover" class="form-label">Background Image for Cover</label>
            <input type="file" class="form-control" id="background_image_cover" name="background_image_cover">
        </div>
        <div class="mb-3">
            <label for="background_image_menu" class="form-label">Background Image for Menu</label>
            <input type="file" class="form-control" id="background_image_menu" name="background_image_menu">
        </div>
        <div class="mb-3">
            <label for="background_image_cocktails" class="form-label">Background Image for {{ $cocktailLabelAdmin }}</label>
            <input type="file" class="form-control" id="background_image_cocktails" name="background_image_cocktails">
        </div>
        <div class="mb-3">
            <label for="background_image_wines" class="form-label">Background Image for Wines</label>
            <input type="file" class="form-control" id="background_image_wines" name="background_image_wines">
        </div>

        <!-- Text Colors -->
        <div class="mb-3">
            <label for="text_color_cover" class="form-label">Text Color for Cover</label>
            <input type="color" class="form-control" id="text_color_cover" name="text_color_cover" value="{{ $settings->text_color_cover ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="text_color_menu" class="form-label">Text Color for Menu</label>
            <input type="color" class="form-control" id="text_color_menu" name="text_color_menu" value="{{ $settings->text_color_menu ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="text_color_cocktails" class="form-label">Text Color for {{ $cocktailLabelAdmin }}</label>
            <input type="color" class="form-control" id="text_color_cocktails" name="text_color_cocktails" value="{{ $settings->text_color_cocktails ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="text_color_wines" class="form-label">Text Color for Wines</label>
            <input type="color" class="form-control" id="text_color_wines" name="text_color_wines" value="{{ $settings->text_color_wines ?? '#000000' }}">
        </div>

        <!-- Font Families -->
        <div class="mb-3">
            <label for="font_family_cover" class="form-label">Font Family for Cover</label>
            <input type="text" class="form-control" id="font_family_cover" name="font_family_cover" value="{{ $settings->font_family_cover ?? 'Arial' }}">
        </div>
        <div class="mb-3">
            <label for="font_family_menu" class="form-label">Font Family for Menu</label>
            <input type="text" class="form-control" id="font_family_menu" name="font_family_menu" value="{{ $settings->font_family_menu ?? 'Arial' }}">
        </div>
        <div class="mb-3">
            <label for="font_family_cocktails" class="form-label">Font Family for {{ $cocktailLabelAdmin }}</label>
            <input type="text" class="form-control" id="font_family_cocktails" name="font_family_cocktails" value="{{ $settings->font_family_cocktails ?? 'Arial' }}">
        </div>
        <div class="mb-3">
            <label for="font_family_wines" class="form-label">Font Family for Wines</label>
            <input type="text" class="form-control" id="font_family_wines" name="font_family_wines" value="{{ $settings->font_family_wines ?? 'Arial' }}">
        </div>

        <!-- Card Opacity -->
        <div class="mb-3">
            <label for="card_opacity_cover" class="form-label">Card Opacity for Cover</label>
            <input type="number" step="0.1" class="form-control" id="card_opacity_cover" name="card_opacity_cover" value="{{ $settings->card_opacity_cover ?? 1 }}">
        </div>
        <div class="mb-3">
            <label for="card_opacity_menu" class="form-label">Card Opacity for Menu</label>
            <input type="number" step="0.1" class="form-control" id="card_opacity_menu" name="card_opacity_menu" value="{{ $settings->card_opacity_menu ?? 1 }}">
        </div>
        <div class="mb-3">
            <label for="card_opacity_cocktails" class="form-label">Card Opacity for {{ $cocktailLabelAdmin }}</label>
            <input type="number" step="0.1" class="form-control" id="card_opacity_cocktails" name="card_opacity_cocktails" value="{{ $settings->card_opacity_cocktails ?? 1 }}">
        </div>
        <div class="mb-3">
            <label for="card_opacity_wines" class="form-label">Card Opacity for Wines</label>
            <input type="number" step="0.1" class="form-control" id="card_opacity_wines" name="card_opacity_wines" value="{{ $settings->card_opacity_wines ?? 1 }}">
        </div>

        <!-- Button Colors -->
        <div class="mb-3">
            <label for="button_color_cover" class="form-label">Button Color for Cover</label>
            <input type="color" class="form-control" id="button_color_cover" name="button_color_cover" value="{{ $settings->button_color_cover ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="button_color_menu" class="form-label">Button Color for Menu</label>
            <input type="color" class="form-control" id="button_color_menu" name="button_color_menu" value="{{ $settings->button_color_menu ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="button_color_cocktails" class="form-label">Button Color for {{ $cocktailLabelAdmin }}</label>
            <input type="color" class="form-control" id="button_color_cocktails" name="button_color_cocktails" value="{{ $settings->button_color_cocktails ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="button_color_wines" class="form-label">Button Color for Wines</label>
            <input type="color" class="form-control" id="button_color_wines" name="button_color_wines" value="{{ $settings->button_color_wines ?? '#000000' }}">
        </div>

        <!-- Category Styles for Menu -->
        <div class="mb-3">
            <label for="category_name_bg_color_menu" class="form-label">Category Background Color (Menu)</label>
            <input type="color" class="form-control" id="category_name_bg_color_menu" name="category_name_bg_color_menu" value="{{ $settings->category_name_bg_color_menu ?? '#ffffff' }}">
        </div>
        <div class="mb-3">
            <label for="category_name_text_color_menu" class="form-label">Category Text Color (Menu)</label>
            <input type="color" class="form-control" id="category_name_text_color_menu" name="category_name_text_color_menu" value="{{ $settings->category_name_text_color_menu ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="category_name_font_size_menu" class="form-label">Category Font Size (Menu)</label>
            <input type="number" class="form-control" id="category_name_font_size_menu" name="category_name_font_size_menu" value="{{ $settings->category_name_font_size_menu ?? 16 }}">
        </div>

        <!-- Repeat for Cocktails and Wines -->
        <div class="mb-3">
            <label for="category_name_bg_color_cocktails" class="form-label">Category Background Color ({{ $cocktailLabelAdmin }})</label>
            <input type="color" class="form-control" id="category_name_bg_color_cocktails" name="category_name_bg_color_cocktails" value="{{ $settings->category_name_bg_color_cocktails ?? '#ffffff' }}">
        </div>
        <div class="mb-3">
            <label for="category_name_text_color_cocktails" class="form-label">Category Text Color ({{ $cocktailLabelAdmin }})</label>
            <input type="color" class="form-control" id="category_name_text_color_cocktails" name="category_name_text_color_cocktails" value="{{ $settings->category_name_text_color_cocktails ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="category_name_font_size_cocktails" class="form-label">Category Font Size ({{ $cocktailLabelAdmin }})</label>
            <input type="number" class="form-control" id="category_name_font_size_cocktails" name="category_name_font_size_cocktails" value="{{ $settings->category_name_font_size_cocktails ?? 16 }}">
        </div>

        <div class="mb-3">
            <label for="category_name_bg_color_wines" class="form-label">Category Background Color (Wines)</label>
            <input type="color" class="form-control" id="category_name_bg_color_wines" name="category_name_bg_color_wines" value="{{ $settings->category_name_bg_color_wines ?? '#ffffff' }}">
        </div>
        <div class="mb-3">
            <label for="category_name_text_color_wines" class="form-label">Category Text Color (Wines)</label>
            <input type="color" class="form-control" id="category_name_text_color_wines" name="category_name_text_color_wines" value="{{ $settings->category_name_text_color_wines ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="category_name_font_size_wines" class="form-label">Category Font Size (Wines)</label>
            <input type="number" class="form-control" id="category_name_font_size_wines" name="category_name_font_size_wines" value="{{ $settings->category_name_font_size_wines ?? 16 }}">
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
</body>
</html>
