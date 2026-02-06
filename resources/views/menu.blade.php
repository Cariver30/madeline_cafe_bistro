<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @php
        $appName = config('app.name', 'Madeleine Cafe Bistro');
        $menuLabel = trim($settings->tab_label_menu ?? $settings->button_label_menu ?? 'Menú');
        $seoTitle = $appName . ' · ' . $menuLabel . ' · Desayunos, brunch y platos creativos';
        $seoDescription = $appName . ' prepara café, desayunos, brunch y una variedad de platos creativos.';
        $seoImage = $settings?->logo
            ? asset('storage/' . $settings->logo)
            : asset('storage/default-logo.png');
        $orderChannel = $orderChannel ?? (!empty($orderMode) ? 'table' : 'view');
        $scopeLabels = [
            'menu' => $settings->tab_label_menu ?? $settings->button_label_menu ?? 'Menú',
            'cocktails' => $settings->tab_label_cocktails ?? $settings->button_label_cocktails ?? 'Cócteles',
            'wines' => $settings->tab_label_wines ?? $settings->button_label_wines ?? 'Café & Brunch',
            'cantina' => $settings->tab_label_cantina ?? $settings->button_label_cantina ?? 'Cantina',
        ];
        $enabledScopes = collect([
            'menu' => $settings->show_tab_menu ?? true,
            'cocktails' => $settings->show_tab_cocktails ?? true,
            'wines' => $settings->show_tab_wines ?? true,
            'cantina' => $settings->show_tab_cantina ?? true,
        ])->filter(fn ($show) => (bool) $show)->keys();
        $scopesWithCategories = collect($categories ?? [])
            ->map(fn ($category) => $category->scope ?? 'menu')
            ->unique()
            ->values();
        $scopeList = $enabledScopes
            ->merge($scopesWithCategories)
            ->unique()
            ->values()
            ->all();
        $defaultScope = in_array('menu', $scopeList, true) ? 'menu' : ($scopeList[0] ?? 'menu');
        $showScopeTabs = count($scopeList) > 1;
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}" />
    <meta property="og:title" content="{{ $seoTitle }}" />
    <meta property="og:description" content="{{ $seoDescription }}" />
    <meta property="og:type" content="website" />
    <meta property="og:image" content="{{ $seoImage }}" />
    <meta property="og:site_name" content="{{ $appName }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $seoTitle }}" />
    <meta name="twitter:description" content="{{ $seoDescription }}" />
    <meta name="twitter:image" content="{{ $seoImage }}" />
    <link rel="icon" href="{{ $seoImage }}" />
    <link rel="apple-touch-icon" href="{{ $seoImage }}" />

    <!-- Tailwind + Flowbite -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/flowbite@2.3.0/dist/flowbite.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <style>
        :root {
            --menu-text-color: {{ $settings->text_color_menu ?? '#ffffff' }};
            --menu-accent-color: {{ $settings->button_color_menu ?? '#FFB347' }};
        }
        html, body {
            min-height: 100vh;
        }

        body {
            font-family: {{ $settings->font_family_menu ?? 'ui-sans-serif' }};
            @if($settings && $settings->background_image_menu)
                background: none;
            @else
                background: radial-gradient(circle at top, #f3eada, #d9c7a1);
            @endif
            background-size: cover;
            background-attachment: fixed;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            @if($settings && $settings->background_image_menu)
                background: url('{{ asset('storage/' . $settings->background_image_menu) }}') no-repeat center center;
                background-size: cover;
            @else
                background: rgba(0, 0, 0, 0.45);
            @endif
            z-index: -1;
        }

        .content-layer {
            position: relative;
            z-index: 1;
        }

        html {
            scroll-behavior: smooth;
        }

        .category-nav-link {
            transition: color 0.3s ease, transform 0.3s ease;
            transform-origin: left center;
        }

        .category-nav-link.active {
            color: {{ $settings->button_color_menu ?? '#FFB347' }};
            transform: scale(1.05);
            font-weight: 600;
        }

        .dish-card {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
            color: var(--menu-text-color);
        }

        .dish-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .subcategory-title {
            color: var(--menu-accent-color);
            font-weight: 700;
            font-size: 1.05rem;
            margin: 0 0 1rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            background-color: rgba(0, 0, 0, 0.25);
        }

        .subcategory-tabs {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.5rem;
            justify-content: center;
            margin: 0 0 1.25rem;
            overflow-x: auto;
            padding: 0.2rem 0.4rem 0.4rem;
            scroll-snap-type: x mandatory;
        }

        .subcategory-tab {
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(0, 0, 0, 0.35);
            color: var(--menu-text-color);
            font-weight: 600;
            font-size: 0.85rem;
            transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease;
            white-space: nowrap;
            scroll-snap-align: start;
        }

        .subcategory-tab:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.12);
        }

        .subcategory-tab.active {
            background: var(--menu-accent-color);
            color: #111827;
            border-color: transparent;
        }

        .subcategory-panel.hidden {
            display: none;
        }

        .hero-media {
            width: 100%;
            max-height: 420px;
            aspect-ratio: 16 / 9;
            object-fit: cover;
        }

        @media (max-width: 768px) {
            body {
                background-position: center top;
                background-attachment: fixed;
            }
        }

        .scope-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .scope-tab {
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(0, 0, 0, 0.35);
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
            transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease;
        }

        .scope-tab:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.12);
        }

        .scope-tab.active {
            background: {{ $settings->button_color_menu ?? '#FFB347' }};
            color: #111827;
            border-color: transparent;
        }

        .scope-tabs {
            scrollbar-width: none;
        }

        .scope-tabs::-webkit-scrollbar {
            display: none;
        }

        @media (max-width: 768px) {
            .scope-tabs {
                flex-wrap: nowrap;
                justify-content: flex-start;
                overflow-x: auto;
                padding: 0.25rem 0.5rem 0.5rem;
                scroll-snap-type: x mandatory;
            }

            .scope-tab {
                white-space: nowrap;
                scroll-snap-align: start;
            }
        }
    </style>
</head>
<body class="text-white bg-black/70">

<!-- LOGO + BOTÓN MENU -->
<div class="text-center py-6 relative content-layer">
    <img src="{{ asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}" class="mx-auto h-28" alt="Logo del Restaurante">

    <!-- Toggle menú -->
    <button id="toggleMenu"
        class="fixed left-4 top-4 z-50 w-12 h-12 rounded-full flex items-center justify-center text-xl shadow-lg text-white lg:hidden"
        style="background-color: {{ $settings->button_color_menu ?? '#000' }};">
        🍽️
    </button>

    <!-- Toggle menú desktop -->
    <button id="toggleDesktopMenu"
        class="hidden lg:flex fixed left-6 top-6 z-40 w-12 h-12 rounded-full items-center justify-center text-lg shadow-lg text-white transition hover:scale-105"
        style="background-color: {{ $settings->button_color_menu ?? '#000' }};">
        ☰
    </button>

    <!-- Menú lateral desktop -->
    <div id="desktopSidebar" class="hidden lg:block">
        <div id="desktopSidebarPanel"
             class="fixed top-0 left-0 h-full w-64 bg-white text-black p-6 space-y-2 shadow-lg overflow-y-auto transition-transform duration-300 ease-in-out lg:translate-x-0">
            @foreach ($categories as $category)
                <a href="#{{ $category->key ?? ('category' . $category->id) }}"
                   class="block text-lg font-semibold hover:text-blue-500 category-nav-link"
                   data-category-target="{{ $category->key ?? ('category' . $category->id) }}"
                   data-scope="{{ $category->scope ?? 'menu' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </div>
</div>

@if($settings->menu_hero_image)
    <div class="max-w-4xl mx-auto px-4 pb-8 content-layer">
        <img src="{{ asset('storage/' . $settings->menu_hero_image) }}" alt="Destacado del menú" class="hero-media rounded-3xl shadow-2xl border border-white/10">
    </div>
@endif

@if (($orderChannel ?? 'view') === 'online' && isset($onlineOrdering) && !($onlineOrdering['enabled'] ?? true))
    <div class="max-w-3xl mx-auto px-4 pb-6 content-layer">
        <div class="rounded-2xl bg-amber-200/90 px-4 py-3 text-center text-sm font-semibold text-slate-900 shadow-lg">
            {{ $onlineOrdering['message'] ?? 'Por el momento no estamos tomando órdenes en línea.' }}
        </div>
    </div>
@endif

@if (empty($orderMode) && !empty($tableSession))
    <div class="max-w-3xl mx-auto px-4 pb-6 content-layer">
        <div class="rounded-2xl bg-amber-400/90 px-4 py-3 text-center text-sm font-semibold text-slate-900 shadow-lg">
            Esta mesa está en modo tradicional. Pide al mesero para ordenar.
        </div>
    </div>
@endif

@if($showScopeTabs && empty($orderMode))
    <div class="max-w-5xl mx-auto px-4 content-layer">
        <div class="scope-tabs" id="scopeTabs" data-default-scope="{{ $defaultScope }}">
            @foreach($scopeList as $scope)
                <button type="button" class="scope-tab {{ $scope === $defaultScope ? 'active' : '' }}" data-scope-tab="{{ $scope }}">
                    {{ $scopeLabels[$scope] ?? ucfirst($scope) }}
                </button>
            @endforeach
        </div>
    </div>
@endif

<!-- Menú flotante móvil -->
<div id="categoryMenu"
    class="lg:hidden fixed inset-0 bg-white text-slate-900 px-6 py-8 space-y-6 overflow-y-auto transform -translate-y-full transition-transform duration-300 z-[60]">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold tracking-[0.25em] uppercase text-slate-500">Categorías</h2>
        <button id="closeMenu" class="text-2xl text-slate-500 hover:text-slate-900">&times;</button>
    </div>
    <div class="grid grid-cols-2 gap-4">
        @foreach ($categories as $category)
            <button class="rounded-2xl border border-slate-200 py-4 px-3 text-sm font-semibold text-left shadow bg-white hover:bg-slate-50 category-nav-link"
                    data-category-target="{{ $category->key ?? ('category' . $category->id) }}"
                    data-scope="{{ $category->scope ?? 'menu' }}">
                {{ $category->name }}
            </button>
        @endforeach
    </div>
</div>
<div id="menuOverlay" class="fixed inset-0 bg-black/60 z-50 hidden lg:hidden"></div>

<!-- Carrusel de chips -->
<div class="lg:hidden content-layer sticky top-20 z-30 px-4">
    <div class="relative">
        <button type="button"
            class="absolute left-0 top-1/2 -translate-y-1/2 z-10 h-9 w-9 rounded-full bg-black/60 text-white shadow-lg"
            data-scroll-target="categoryChipRow"
            data-scroll-direction="left"
            aria-label="Anterior">
            <i class="fas fa-chevron-left text-sm"></i>
        </button>
        <div id="categoryChipRow" class="flex gap-3 overflow-x-auto py-3 snap-x snap-mandatory scroll-smooth">
            @foreach ($categories as $category)
                <button class="category-chip snap-start whitespace-nowrap px-4 py-2 rounded-full border border-white/20 bg-black/40 text-sm font-semibold backdrop-blur-md hover:scale-105 transition category-nav-link"
                        data-category-target="{{ $category->key ?? ('category' . $category->id) }}"
                        data-scope="{{ $category->scope ?? 'menu' }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
        <button type="button"
            class="absolute right-0 top-1/2 -translate-y-1/2 z-10 h-9 w-9 rounded-full bg-black/60 text-white shadow-lg"
            data-scroll-target="categoryChipRow"
            data-scroll-direction="right"
            aria-label="Siguiente">
            <i class="fas fa-chevron-right text-sm"></i>
        </button>
    </div>
</div>

<!-- CONTENIDO DE CATEGORÍAS Y PLATOS -->
<div class="max-w-5xl mx-auto px-4 {{ !empty($orderMode) ? 'pb-40' : 'pb-32' }} content-layer">
    @if($showScopeTabs)
        @foreach($scopeList as $scope)
            @if(!in_array($scope, $scopesWithCategories->all(), true))
                <section class="mb-10 category-section hidden" data-scope="{{ $scope }}">
                    <div class="rounded-2xl bg-black/40 p-6 text-center text-sm font-semibold text-white/80">
                        {{ $scopeLabels[$scope] ?? ucfirst($scope) }} no tiene productos disponibles por ahora.
                    </div>
                </section>
            @endif
        @endforeach
    @endif
    @foreach ($categories as $category)
        <section id="{{ $category->key ?? ('category' . $category->id) }}"
                 class="mb-10 category-section"
                 data-category-id="{{ $category->key ?? ('category' . $category->id) }}"
                 data-scope="{{ $category->scope ?? 'menu' }}">
            <h2 class="text-3xl font-bold text-center mb-6"
                style="background-color: {{ $settings->category_name_bg_color_menu ?? 'rgba(254, 90, 90, 0.8)' }};
                       color: {{ $settings->category_name_text_color_menu ?? '#f9f9f9' }};
                       font-size: {{ $settings->category_name_font_size_menu ?? 30 }}px;
                       border-radius: 10px; padding: 10px;">
                {{ $category->name }}
            </h2>

            @php
                $categoryItems = ($category->items ?? $category->dishes ?? collect())
                    ->where('visible', true);
                $subcategories = $category->subcategories ?? collect();
                $uncategorizedItems = $categoryItems->whereNull('subcategory_id');
                $scope = $category->scope ?? 'menu';
                $subcategoryBg = match ($scope) {
                    'cocktails' => $settings->subcategory_name_bg_color_cocktails ?? 'rgba(0, 0, 0, 0.25)',
                    'wines' => $settings->subcategory_name_bg_color_wines ?? 'rgba(0, 0, 0, 0.25)',
                    default => $settings->subcategory_name_bg_color_menu ?? 'rgba(0, 0, 0, 0.25)',
                };
                $subcategoryText = match ($scope) {
                    'cocktails' => $settings->subcategory_name_text_color_cocktails ?? ($settings->text_color_cocktails ?? '#ffffff'),
                    'wines' => $settings->subcategory_name_text_color_wines ?? ($settings->text_color_wines ?? '#ffffff'),
                    default => $settings->subcategory_name_text_color_menu ?? ($settings->text_color_menu ?? '#ffffff'),
                };
            @endphp
            @if($subcategories->count())
                @php
                    $subcategoryGroups = collect();
                    foreach ($subcategories as $subcategory) {
                        $subcategoryItems = ($subcategory->dishes ?? $subcategory->items ?? collect())
                            ->where('visible', true);
                        $sameName = trim(mb_strtolower((string) $subcategory->name)) === trim(mb_strtolower((string) $category->name));
                        if ($subcategoryItems->isNotEmpty() && ! $sameName) {
                            $subcategoryGroups->push([
                                'id' => $subcategory->id,
                                'name' => $subcategory->name,
                                'items' => $subcategoryItems,
                            ]);
                        } elseif ($subcategoryItems->isNotEmpty() && $sameName) {
                            $uncategorizedItems = $uncategorizedItems->merge($subcategoryItems);
                        }
                    }
                    $hasSubcategoryGroups = $subcategoryGroups->isNotEmpty();
                    $includeOtherTab = $hasSubcategoryGroups && $uncategorizedItems->count();
                    $tabGroups = $includeOtherTab
                        ? $subcategoryGroups->merge([['id' => 'other', 'name' => 'Otros', 'items' => $uncategorizedItems]])
                        : $subcategoryGroups;
                    $showTabs = $tabGroups->count() > 1;
                @endphp
                @if($hasSubcategoryGroups)
                    @if($showTabs)
                        <div class="subcategory-tabs" data-category-tabs="{{ $category->id }}">
                            @foreach ($tabGroups as $group)
                                <button type="button"
                                    class="subcategory-tab {{ $loop->first ? 'active' : '' }}"
                                    data-category-tab="{{ $category->id }}"
                                    data-subcategory-tab="{{ $group['id'] }}">
                                    {{ $group['name'] }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @foreach ($tabGroups as $group)
                        <div class="subcategory-panel {{ $loop->first ? '' : 'hidden' }}"
                             data-category-panel="{{ $category->id }}"
                             data-subcategory-panel="{{ $group['id'] }}">
                            @if(! $showTabs)
                                <h3 class="subcategory-title" style="background-color: {{ $subcategoryBg }}; color: {{ $subcategoryText }};">
                                    {{ $group['name'] }}
                                </h3>
                            @endif
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            @foreach ($group['items'] as $item)
                                @php
                                    $itemType = ($category->scope ?? 'menu') === 'cocktails'
                                        ? 'cocktail'
                                        : (($category->scope ?? 'menu') === 'wines'
                                            ? 'wine'
                                            : (($category->scope ?? 'menu') === 'cantina' ? 'cantina' : 'dish'));
                                    $itemIdPrefix = $itemType === 'dish' ? 'dish' : $itemType;
                                    $itemExtras = $item->extras->where('active', true);
                                    $itemExtrasPayload = $itemExtras->map(function ($extra) {
                                        return [
                                            'id' => $extra->id,
                                            'name' => $extra->name,
                                            'group_name' => $extra->group_name,
                                            'kind' => $extra->kind,
                                            'group_required' => (bool) $extra->group_required,
                                            'max_select' => $extra->max_select,
                                            'min_select' => $extra->min_select,
                                            'price' => number_format($extra->price, 2, '.', ''),
                                            'description' => $extra->description,
                                        ];
                                    });
                                    $winePairs = method_exists($item, 'wines') && $item->wines
                                        ? $item->wines->map(fn($wine) => $wine->id.'::'.$wine->name)->implode('|')
                                        : '';
                                    $recommendedPairs = method_exists($item, 'recommendedDishes') && $item->recommendedDishes
                                        ? $item->recommendedDishes->map(fn($recommended) => $recommended->id.'::'.$recommended->name)->implode('|')
                                        : '';
                                    $upsellPayload = [];
                                    if ($itemType === 'dish') {
                                        foreach ($item->recommendedDishes ?? [] as $recommended) {
                                            $upsellPayload[] = [
                                                'type' => 'dish',
                                                'id' => $recommended->id,
                                                'name' => $recommended->name,
                                                'price' => (float) ($recommended->price ?? 0),
                                            ];
                                        }
                                        foreach ($item->wines ?? [] as $wine) {
                                            $upsellPayload[] = [
                                                'type' => 'wine',
                                                'id' => $wine->id,
                                                'name' => $wine->name,
                                                'price' => (float) ($wine->price ?? 0),
                                            ];
                                        }
                                        foreach ($item->cocktails ?? [] as $cocktail) {
                                            $upsellPayload[] = [
                                                'type' => 'cocktail',
                                                'id' => $cocktail->id,
                                                'name' => $cocktail->name,
                                                'price' => (float) ($cocktail->price ?? 0),
                                            ];
                                        }
                                    } else {
                                        foreach ($item->dishes ?? [] as $dish) {
                                            $upsellPayload[] = [
                                                'type' => 'dish',
                                                'id' => $dish->id,
                                                'name' => $dish->name,
                                                'price' => (float) ($dish->price ?? 0),
                                            ];
                                        }
                                    }
                                    $upsellPayload = collect($upsellPayload)
                                        ->unique(fn ($upsell) => $upsell['type'] . '-' . $upsell['id'])
                                        ->values()
                                        ->all();
                                    $itemTaxesPayload = collect($item->taxes ?? [])
                                        ->merge($category->taxes ?? collect())
                                        ->filter(fn($tax) => (bool) ($tax->active ?? true))
                                        ->unique('id')
                                        ->map(fn($tax) => [
                                            'id' => $tax->id,
                                            'name' => $tax->name,
                                            'rate' => (float) $tax->rate,
                                        ])
                                        ->values()
                                        ->all();
                                @endphp
                                <div id="{{ $itemIdPrefix }}{{ $item->id }}" onclick="openDishModal(this)"
                                    class="dish-card rounded-lg p-4 shadow-lg relative flex items-center cursor-pointer hover:scale-105 transition"
                                    style="background-color: {{ $settings->card_bg_color_menu ?? '#191919' }};
                                           opacity: {{ $settings->card_opacity_menu ?? 0.9 }};"
                                    data-item-type="{{ $itemType }}"
                                    data-item-id="{{ $item->id }}"
                                    data-name="{{ $item->name }}"
                                    data-description="{{ $item->description }}"
                                    data-price="${{ number_format($item->price, 2) }}"
                                    data-image="{{ $item->image ? asset('storage/' . $item->image) : asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}"
                                    data-wines="{{ e($winePairs) }}"
                                    data-recommended="{{ e($recommendedPairs) }}"
                                    data-upsells='@json($upsellPayload)'
                                    data-extras='@json($itemExtrasPayload)'
                                    data-taxes='@json($itemTaxesPayload)'>

                                    <span class="absolute top-2 right-2 text-xs bg-gray-700 text-white px-2 py-1 rounded">Ver más</span>


                                    <img src="{{ $item->image ? asset('storage/' . $item->image) : asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}"
                                         alt="{{ $item->name }}"
                                         class="h-24 w-24 rounded-full object-cover mr-4 border border-white/10">

                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold">{{ $item->name }}</h3>
                                        <p class="text-sm mb-2">${{ number_format($item->price, 2) }}</p>


                                        @if ($itemType === 'dish' && $item->wines && $item->wines->count())
                                            <div class="mt-3">
                                                <p class="text-xs uppercase tracking-[0.2em] mb-2" style="color: {{ $settings->text_color_menu ?? '#fefefe' }};">Maridajes sugeridos</p>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($item->wines as $wine)
                                                        @if (!empty($orderMode))
                                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border"
                                                                style="background-color: {{ $settings->category_name_bg_color_menu ?? 'rgba(254, 90, 90, 0.2)' }}; border-color: {{ $settings->button_color_menu ?? '#FFB347' }}; color: {{ $settings->text_color_menu ?? '#ffffff' }};">
                                                                <i class="fas fa-wine-glass-alt" style="color: {{ $settings->button_color_menu ?? '#FFB347' }};"></i>
                                                                {{ $wine->name }}
                                                            </span>
                                                        @else
                                                            <a href="{{ route('coffee.index') }}#drink{{ $wine->id }}"
                                                               class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border transition hover:scale-105"
                                                               style="background-color: {{ $settings->category_name_bg_color_menu ?? 'rgba(254, 90, 90, 0.2)' }}; border-color: {{ $settings->button_color_menu ?? '#FFB347' }}; color: {{ $settings->text_color_menu ?? '#ffffff' }};">
                                                                <i class="fas fa-wine-glass-alt" style="color: {{ $settings->button_color_menu ?? '#FFB347' }};"></i>
                                                                {{ $wine->name }}
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    @php
                        $fallbackItems = $categoryItems->merge($uncategorizedItems)->unique('id');
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($fallbackItems as $item)
                            @php
                                $itemType = ($category->scope ?? 'menu') === 'cocktails'
                                    ? 'cocktail'
                                    : (($category->scope ?? 'menu') === 'wines'
                                        ? 'wine'
                                        : (($category->scope ?? 'menu') === 'cantina' ? 'cantina' : 'dish'));
                                $itemIdPrefix = $itemType === 'dish' ? 'dish' : $itemType;
                                $itemExtras = $item->extras->where('active', true);
                                $itemExtrasPayload = $itemExtras->map(function ($extra) {
                                    return [
                                        'id' => $extra->id,
                                        'name' => $extra->name,
                                        'group_name' => $extra->group_name,
                                        'kind' => $extra->kind,
                                        'group_required' => (bool) $extra->group_required,
                                        'max_select' => $extra->max_select,
                                        'min_select' => $extra->min_select,
                                        'price' => number_format($extra->price, 2, '.', ''),
                                        'description' => $extra->description,
                                    ];
                                });
                                $winePairs = method_exists($item, 'wines') && $item->wines
                                    ? $item->wines->map(fn($wine) => $wine->id.'::'.$wine->name)->implode('|')
                                    : '';
                                $recommendedPairs = method_exists($item, 'recommendedDishes') && $item->recommendedDishes
                                    ? $item->recommendedDishes->map(fn($recommended) => $recommended->id.'::'.$recommended->name)->implode('|')
                                    : '';
                                $upsellPayload = [];
                                if ($itemType === 'dish') {
                                    foreach ($item->recommendedDishes ?? [] as $recommended) {
                                        $upsellPayload[] = [
                                            'type' => 'dish',
                                            'id' => $recommended->id,
                                            'name' => $recommended->name,
                                            'price' => (float) ($recommended->price ?? 0),
                                        ];
                                    }
                                    foreach ($item->wines ?? [] as $wine) {
                                        $upsellPayload[] = [
                                            'type' => 'wine',
                                            'id' => $wine->id,
                                            'name' => $wine->name,
                                            'price' => (float) ($wine->price ?? 0),
                                        ];
                                    }
                                    foreach ($item->cocktails ?? [] as $cocktail) {
                                        $upsellPayload[] = [
                                            'type' => 'cocktail',
                                            'id' => $cocktail->id,
                                            'name' => $cocktail->name,
                                            'price' => (float) ($cocktail->price ?? 0),
                                        ];
                                    }
                                } else {
                                    foreach ($item->dishes ?? [] as $dish) {
                                        $upsellPayload[] = [
                                            'type' => 'dish',
                                            'id' => $dish->id,
                                            'name' => $dish->name,
                                            'price' => (float) ($dish->price ?? 0),
                                        ];
                                    }
                                }
                                $upsellPayload = collect($upsellPayload)
                                    ->unique(fn ($upsell) => $upsell['type'] . '-' . $upsell['id'])
                                    ->values()
                                    ->all();
                                $itemTaxesPayload = collect($item->taxes ?? [])
                                    ->merge($category->taxes ?? collect())
                                    ->filter(fn($tax) => (bool) ($tax->active ?? true))
                                    ->unique('id')
                                    ->map(fn($tax) => [
                                        'id' => $tax->id,
                                        'name' => $tax->name,
                                        'rate' => (float) $tax->rate,
                                    ])
                                    ->values()
                                    ->all();
                            @endphp
                            <div id="{{ $itemIdPrefix }}{{ $item->id }}" onclick="openDishModal(this)"
                                class="dish-card rounded-lg p-4 shadow-lg relative flex items-center cursor-pointer hover:scale-105 transition"
                                style="background-color: {{ $settings->card_bg_color_menu ?? '#191919' }};
                                       opacity: {{ $settings->card_opacity_menu ?? 0.9 }};"
                            data-item-type="{{ $itemType }}"
                            data-item-id="{{ $item->id }}"
                            data-name="{{ $item->name }}"
                            data-description="{{ $item->description }}"
                            data-price="${{ number_format($item->price, 2) }}"
                            data-image="{{ $item->image ? asset('storage/' . $item->image) : asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}"
                                data-wines="{{ e($winePairs) }}"
                                data-recommended="{{ e($recommendedPairs) }}"
                                data-upsells='@json($upsellPayload)'
                                data-extras='@json($itemExtrasPayload)'
                                data-taxes='@json($itemTaxesPayload)'>

                            <span class="absolute top-2 right-2 text-xs bg-gray-700 text-white px-2 py-1 rounded">Ver más</span>


                            <img src="{{ $item->image ? asset('storage/' . $item->image) : asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}"
                                 alt="{{ $item->name }}"
                                 class="h-24 w-24 rounded-full object-cover mr-4 border border-white/10">

                            <div class="flex-1">
                                <h3 class="text-xl font-bold">{{ $item->name }}</h3>
                                <p class="text-sm mb-2">${{ number_format($item->price, 2) }}</p>


                                @if ($itemType === 'dish' && $item->wines && $item->wines->count())
                                    <div class="mt-3">
                                        <p class="text-xs uppercase tracking-[0.2em] mb-2" style="color: {{ $settings->text_color_menu ?? '#fefefe' }};">Maridajes sugeridos</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($item->wines as $wine)
                                                @if (!empty($orderMode))
                                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border"
                                                        style="background-color: {{ $settings->category_name_bg_color_menu ?? 'rgba(254, 90, 90, 0.2)' }}; border-color: {{ $settings->button_color_menu ?? '#FFB347' }}; color: {{ $settings->text_color_menu ?? '#ffffff' }};">
                                                        <i class="fas fa-wine-glass-alt" style="color: {{ $settings->button_color_menu ?? '#FFB347' }};"></i>
                                                        {{ $wine->name }}
                                                    </span>
                                                @else
                                                    <a href="{{ route('coffee.index') }}#drink{{ $wine->id }}"
                                                       class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border transition hover:scale-105"
                                                       style="background-color: {{ $settings->category_name_bg_color_menu ?? 'rgba(254, 90, 90, 0.2)' }}; border-color: {{ $settings->button_color_menu ?? '#FFB347' }}; color: {{ $settings->text_color_menu ?? '#ffffff' }};">
                                                        <i class="fas fa-wine-glass-alt" style="color: {{ $settings->button_color_menu ?? '#FFB347' }};"></i>
                                                        {{ $wine->name }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($categoryItems as $item)
                        @php
                            $itemType = ($category->scope ?? 'menu') === 'cocktails'
                                ? 'cocktail'
                                : (($category->scope ?? 'menu') === 'wines'
                                    ? 'wine'
                                    : (($category->scope ?? 'menu') === 'cantina' ? 'cantina' : 'dish'));
                            $itemIdPrefix = $itemType === 'dish' ? 'dish' : $itemType;
                            $itemExtras = $item->extras->where('active', true);
                            $itemExtrasPayload = $itemExtras->map(function ($extra) {
                                return [
                                    'id' => $extra->id,
                                    'name' => $extra->name,
                                'group_name' => $extra->group_name,
                                'kind' => $extra->kind,
                                'group_required' => (bool) $extra->group_required,
                                'max_select' => $extra->max_select,
                                'min_select' => $extra->min_select,
                                'price' => number_format($extra->price, 2, '.', ''),
                                'description' => $extra->description,
                            ];
                        });
                            $winePairs = method_exists($item, 'wines') && $item->wines
                                ? $item->wines->map(fn($wine) => $wine->id.'::'.$wine->name)->implode('|')
                                : '';
                            $recommendedPairs = method_exists($item, 'recommendedDishes') && $item->recommendedDishes
                                ? $item->recommendedDishes->map(fn($recommended) => $recommended->id.'::'.$recommended->name)->implode('|')
                                : '';
                            $upsellPayload = [];
                            if ($itemType === 'dish') {
                                foreach ($item->recommendedDishes ?? [] as $recommended) {
                                    $upsellPayload[] = [
                                        'type' => 'dish',
                                        'id' => $recommended->id,
                                        'name' => $recommended->name,
                                        'price' => (float) ($recommended->price ?? 0),
                                    ];
                                }
                                foreach ($item->wines ?? [] as $wine) {
                                    $upsellPayload[] = [
                                        'type' => 'wine',
                                        'id' => $wine->id,
                                        'name' => $wine->name,
                                        'price' => (float) ($wine->price ?? 0),
                                    ];
                                }
                                foreach ($item->cocktails ?? [] as $cocktail) {
                                    $upsellPayload[] = [
                                        'type' => 'cocktail',
                                        'id' => $cocktail->id,
                                        'name' => $cocktail->name,
                                        'price' => (float) ($cocktail->price ?? 0),
                                    ];
                                }
                            } else {
                                foreach ($item->dishes ?? [] as $dish) {
                                    $upsellPayload[] = [
                                        'type' => 'dish',
                                        'id' => $dish->id,
                                        'name' => $dish->name,
                                        'price' => (float) ($dish->price ?? 0),
                                    ];
                                }
                            }
                            $upsellPayload = collect($upsellPayload)
                                ->unique(fn ($upsell) => $upsell['type'] . '-' . $upsell['id'])
                                ->values()
                                ->all();
                            $itemTaxesPayload = collect($item->taxes ?? [])
                                ->merge($category->taxes ?? collect())
                                ->filter(fn($tax) => (bool) ($tax->active ?? true))
                                ->unique('id')
                                ->map(fn($tax) => [
                                    'id' => $tax->id,
                                    'name' => $tax->name,
                                    'rate' => (float) $tax->rate,
                                ])
                                ->values()
                                ->all();
                        @endphp
                            <div id="{{ $itemIdPrefix }}{{ $item->id }}" onclick="openDishModal(this)"
                                class="dish-card rounded-lg p-4 shadow-lg relative flex items-center cursor-pointer hover:scale-105 transition"
                                style="background-color: {{ $settings->card_bg_color_menu ?? '#191919' }};
                                       opacity: {{ $settings->card_opacity_menu ?? 0.9 }};"
                            data-item-type="{{ $itemType }}"
                            data-item-id="{{ $item->id }}"
                            data-name="{{ $item->name }}"
                            data-description="{{ $item->description }}"
                            data-price="${{ number_format($item->price, 2) }}"
                            data-image="{{ $item->image ? asset('storage/' . $item->image) : asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}"
                                data-wines="{{ e($winePairs) }}"
                                data-recommended="{{ e($recommendedPairs) }}"
                                data-upsells='@json($upsellPayload)'
                                data-extras='@json($itemExtrasPayload)'
                                data-taxes='@json($itemTaxesPayload)'>

                            <span class="absolute top-2 right-2 text-xs bg-gray-700 text-white px-2 py-1 rounded">Ver más</span>


                            <img src="{{ $item->image ? asset('storage/' . $item->image) : asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}"
                                 alt="{{ $item->name }}"
                                 class="h-24 w-24 rounded-full object-cover mr-4 border border-white/10">

                            <div class="flex-1">
                                <h3 class="text-xl font-bold">{{ $item->name }}</h3>
                                <p class="text-sm mb-2">${{ number_format($item->price, 2) }}</p>


                                @if ($itemType === 'dish' && $item->wines && $item->wines->count())
                                    <div class="mt-3">
                                        <p class="text-xs uppercase tracking-[0.2em] mb-2" style="color: {{ $settings->text_color_menu ?? '#fefefe' }};">Maridajes sugeridos</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($item->wines as $wine)
                                                @if (!empty($orderMode))
                                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border"
                                                        style="background-color: {{ $settings->category_name_bg_color_menu ?? 'rgba(254, 90, 90, 0.2)' }}; border-color: {{ $settings->button_color_menu ?? '#FFB347' }}; color: {{ $settings->text_color_menu ?? '#ffffff' }};">
                                                        <i class="fas fa-wine-glass-alt" style="color: {{ $settings->button_color_menu ?? '#FFB347' }};"></i>
                                                        {{ $wine->name }}
                                                    </span>
                                                @else
                                                    <a href="{{ route('coffee.index') }}#drink{{ $wine->id }}"
                                                       class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border transition hover:scale-105"
                                                       style="background-color: {{ $settings->category_name_bg_color_menu ?? 'rgba(254, 90, 90, 0.2)' }}; border-color: {{ $settings->button_color_menu ?? '#FFB347' }}; color: {{ $settings->text_color_menu ?? '#ffffff' }};">
                                                        <i class="fas fa-wine-glass-alt" style="color: {{ $settings->button_color_menu ?? '#FFB347' }};"></i>
                                                        {{ $wine->name }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @endforeach
</div>

<!-- BOTONES FLOTANTES -->
@if (!empty($orderMode) && $showScopeTabs)
    <div class="fixed bottom-5 left-0 right-0 z-50 content-layer px-4">
        <div class="w-full lg:flex lg:justify-center">
            <div class="w-full lg:max-w-4xl lg:mx-auto">
                <div id="scopeTabsBottom"
                    class="scope-tabs scope-tabs-bottom flex-nowrap items-center gap-3 px-4 py-2 rounded-3xl backdrop-blur-lg border border-white/20 shadow-2xl overflow-x-auto scroll-smooth lg:overflow-visible lg:justify-center"
                    style="background-color: {{ $settings->floating_bar_bg_menu ?? 'rgba(0,0,0,0.55)' }};">
                    @foreach($scopeList as $scope)
                        <button type="button"
                            class="scope-tab {{ $scope === $defaultScope ? 'active' : '' }}"
                            data-scope-tab="{{ $scope }}">
                            {{ $scopeLabels[$scope] ?? ucfirst($scope) }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

@if (empty($orderMode))
    @include('components.floating-nav', [
        'settings' => $settings,
        'background' => $settings->floating_bar_bg_menu ?? 'rgba(0,0,0,0.55)',
        'buttonColor' => $settings->button_color_menu ?? '#000'
    ])
@endif

@if (!empty($orderMode))
    <button id="openCartButton"
        class="fixed {{ $showScopeTabs ? 'bottom-24' : 'bottom-6' }} right-6 z-50 rounded-full px-5 py-3 shadow-xl text-sm font-semibold bg-amber-400 text-slate-900 hover:scale-105 transition">
        Pedido (<span id="cartCount">0</span>)
    </button>
@endif

<!-- MODAL DE DETALLE DEL PLATO -->
<div id="dishDetailsModal" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-black/70">
    <div class="relative w-full max-w-xl max-h-[90vh]">
        <div class="bg-white rounded-lg shadow-lg text-gray-900 p-6 relative overflow-y-auto max-h-[90vh]">

            <button onclick="closeDishModal()" class="absolute top-3 right-3 text-gray-500 hover:text-red-600 text-xl font-bold">
                ✕
            </button>

            <img id="modalImage" class="w-full h-60 object-cover rounded-lg mb-4" alt="Imagen del plato">

            <h3 id="modalTitle" class="text-2xl font-bold mb-2"></h3>
            <p id="modalDescription" class="mb-2"></p>
            <p id="modalPrice" class="font-semibold text-lg mb-4"></p>

            <div id="modalWines" class="mt-4 hidden">
                <h4 class="text-lg font-semibold mb-2" style="color: {{ $settings->button_color_menu ?? '#FFB347' }};">Bebidas sugeridas ☕</h4>
                <ul id="wineList" class="list-disc list-inside" style="color: {{ $settings->text_color_menu ?? '#111' }};"></ul>
            </div>

            <div id="modalPairings" class="mt-4 hidden">
                <h4 class="text-lg font-semibold mb-2" style="color: {{ $settings->button_color_menu ?? '#FFB347' }};">Combínalo con</h4>
                <ul id="pairingList" class="list-disc list-inside" style="color: {{ $settings->text_color_menu ?? '#111' }};"></ul>
            </div>

            <div id="modalUpsells" class="mt-4 hidden">
                <h4 class="text-lg font-semibold mb-2" style="color: {{ $settings->button_color_menu ?? '#FFB347' }};">Combínalo con</h4>
                <div id="upsellList" class="flex flex-wrap gap-2"></div>
            </div>

            <div id="modalExtras" class="mt-4 hidden">
                <h4 class="text-lg font-semibold mb-2" style="color: {{ $settings->button_color_menu ?? '#FFB347' }};">Opciones disponibles</h4>
                <ul id="extrasList" class="space-y-2 text-sm" style="color: {{ $settings->text_color_menu ?? '#111' }};"></ul>
            </div>

            @if (!empty($orderMode))
                <div id="orderControls" class="mt-6 border-t border-slate-200 pt-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-slate-700">Cantidad</span>
                        <div class="flex items-center gap-3">
                            <button id="qtyMinus" class="h-8 w-8 rounded-full border border-slate-300 text-slate-600">-</button>
                            <span id="qtyValue" class="text-base font-semibold text-slate-900">1</span>
                            <button id="qtyPlus" class="h-8 w-8 rounded-full border border-slate-300 text-slate-600">+</button>
                        </div>
                    </div>
                    <div>
                        <label for="itemNotes" class="text-sm font-semibold text-slate-700">Notas para el mesero</label>
                        <textarea id="itemNotes" rows="2" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Ej. sin cebolla, bien cocido"></textarea>
                    </div>
                    <button id="addToCartButton" class="w-full rounded-full bg-amber-400 py-3 font-semibold text-slate-900">
                        Agregar al pedido
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

@if (!empty($orderMode))
    <div id="orderCartModal" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-black/70">
        <div class="relative w-full max-w-xl max-h-[90vh]">
            <div class="bg-white rounded-lg shadow-lg text-gray-900 p-6 relative overflow-y-auto max-h-[90vh]">
                <button id="closeCartButton" class="absolute top-3 right-3 text-gray-500 hover:text-red-600 text-xl font-bold">
                    ✕
                </button>

                <h3 class="text-2xl font-bold mb-4">
                    {{ $orderChannel === 'online' ? 'Pedido para llevar' : 'Pedido de la mesa' }}
                </h3>
                <div id="cartItems" class="space-y-3"></div>
                <div id="cartTotals" class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between">
                        <span>Subtotal</span>
                        <span id="cartSubtotal">$0.00</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Impuestos</span>
                        <span id="cartTaxTotal">$0.00</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-base font-semibold text-slate-900">
                        <span>Total</span>
                        <span id="cartTotal">$0.00</span>
                    </div>
                </div>

                @if ($orderChannel === 'online')
                    <div class="mt-4 space-y-3">
                        <div>
                            <label for="onlineCustomerName" class="text-sm font-semibold text-slate-700">Nombre</label>
                            <input id="onlineCustomerName" type="text" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Nombre para la orden">
                        </div>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div>
                                <label for="onlineCustomerEmail" class="text-sm font-semibold text-slate-700">Email (para recibo)</label>
                                <input id="onlineCustomerEmail" type="email" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="correo@ejemplo.com">
                            </div>
                            <div>
                                <label for="onlineCustomerPhone" class="text-sm font-semibold text-slate-700">Teléfono</label>
                                <input id="onlineCustomerPhone" type="tel" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="(787) 000-0000">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div>
                                <label for="onlinePickupAt" class="text-sm font-semibold text-slate-700">Hora de recogido</label>
                                <input id="onlinePickupAt" type="datetime-local" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label for="onlineOrderNotes" class="text-sm font-semibold text-slate-700">Notas</label>
                                <input id="onlineOrderNotes" type="text" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Ej. sin cebolla, llamar al llegar">
                            </div>
                        </div>
                    </div>
                @endif

                @if ($orderChannel === 'online' && isset($onlineOrdering) && !($onlineOrdering['enabled'] ?? true))
                    <div id="onlineOrderingClosedNote" class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        {{ $onlineOrdering['message'] ?? 'Por el momento no estamos tomando órdenes en línea.' }}
                    </div>
                @endif

                <div class="mt-5 flex flex-col gap-3">
                    <button id="sendOrderButton" class="rounded-full bg-amber-400 py-3 text-slate-900 font-semibold">
                        {{ $orderChannel === 'online' ? 'Pagar y enviar' : 'Enviar orden al mesero' }}
                    </button>
                    <button id="clearCartButton" class="rounded-full border border-slate-300 py-2 text-slate-600">
                        Vaciar pedido
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<div id="menuPopupModal" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center px-4">
    <div class="bg-white text-slate-900 rounded-3xl w-full max-w-2xl p-4 relative">
        <button type="button" class="absolute top-3 right-3 text-2xl text-slate-500 hover:text-slate-900" onclick="closeMenuPopup()">&times;</button>
        <div class="space-y-3">
            <h3 id="menuPopupTitle" class="text-xl font-semibold text-center"></h3>
            <img id="menuPopupImage" src="" alt="Anuncio" class="w-full rounded-2xl object-cover">
        </div>
    </div>
</div>

<!-- Flowbite JS -->
<script src="https://unpkg.com/flowbite@2.3.0/dist/flowbite.min.js"></script>

<script>
    const orderMode = @json(!empty($orderMode));
    const orderChannel = @json($orderChannel ?? 'view');
    const orderToken = @json($qrToken ?? null);
    const onlineOrdering = @json($onlineOrdering ?? ['enabled' => true, 'message' => '']);

    let menuPopupInstance;
    let cartModalInstance;
    let activeItem = null;
    let cartItems = [];
    let currentQty = 1;
    document.addEventListener('DOMContentLoaded', function () {
        console.log('✅ Menú cargado con Tailwind y Flowbite');
        if (orderChannel === 'online' && onlineOrdering && onlineOrdering.enabled === false) {
            const sendOrderButton = document.getElementById('sendOrderButton');
            if (sendOrderButton) {
                sendOrderButton.disabled = true;
                sendOrderButton.classList.add('opacity-60', 'cursor-not-allowed');
                sendOrderButton.textContent = 'Ordenes en línea cerradas';
            }
        }

        // Botón toggle menú lateral
        const toggleMenuBtn = document.getElementById('toggleMenu');
        const categoryMenu = document.getElementById('categoryMenu');
        const menuOverlay = document.getElementById('menuOverlay');
        const closeMenuBtn = document.getElementById('closeMenu');
        const navLinks = document.querySelectorAll('.category-nav-link');
        const scopeTabs = document.querySelectorAll('[data-scope-tab]');
        const scopeTabsWrapper = document.getElementById('scopeTabs');
        const categorySections = document.querySelectorAll('.category-section');
        const categoryChipRow = document.getElementById('categoryChipRow');

        const desktopToggleBtn = document.getElementById('toggleDesktopMenu');
        const desktopSidebarPanel = document.getElementById('desktopSidebarPanel');
        let desktopSidebarCollapsed = false;

        document.querySelectorAll('[data-scroll-target="categoryChipRow"]').forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.dataset.scrollTarget;
                const direction = button.dataset.scrollDirection;
                const container = document.getElementById(targetId);
                if (!container) return;
                const offset = container.clientWidth * 0.6;
                container.scrollBy({
                    left: direction === 'left' ? -offset : offset,
                    behavior: 'smooth',
                });
            });
        });

        const setActiveScope = (scope, options = {}) => {
            if (!scope) return;
            const shouldScroll = options.scroll !== false;
            scopeTabs.forEach(tab => {
                tab.classList.toggle('active', tab.dataset.scopeTab === scope);
            });
            navLinks.forEach(link => {
                const linkScope = link.dataset.scope || 'menu';
                const isMatch = linkScope === scope;
                link.classList.toggle('hidden', !isMatch);
                if (!isMatch) {
                    link.classList.remove('active');
                }
            });
            categorySections.forEach(section => {
                const sectionScope = section.dataset.scope || 'menu';
                section.classList.toggle('hidden', sectionScope !== scope);
            });
            if (categoryChipRow) {
                categoryChipRow.scrollTo({ left: 0, behavior: 'smooth' });
            }
            if (shouldScroll) {
                const firstVisible = document.querySelector(`.category-section[data-scope="${scope}"]`);
                if (firstVisible) {
                    firstVisible.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        };

        if (scopeTabs.length > 0) {
            const defaultScope = scopeTabsWrapper?.dataset?.defaultScope || scopeTabs[0]?.dataset?.scopeTab;
            setActiveScope(defaultScope, { scroll: false });
            scopeTabs.forEach(tab => {
                tab.addEventListener('click', () => setActiveScope(tab.dataset.scopeTab));
            });
        }

        const setDesktopSidebarState = (collapsed) => {
            desktopSidebarCollapsed = collapsed;
            if (collapsed) {
                desktopSidebarPanel?.classList.add('-translate-x-full');
            } else {
                desktopSidebarPanel?.classList.remove('-translate-x-full');
            }
            if (desktopToggleBtn) {
                desktopToggleBtn.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
            }
        };

        desktopToggleBtn?.addEventListener('click', () => {
            setDesktopSidebarState(!desktopSidebarCollapsed);
        });

        const openMenu = () => {
            categoryMenu?.classList.remove('-translate-y-full');
            menuOverlay?.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        };

        const closeMenu = () => {
            categoryMenu?.classList.add('-translate-y-full');
            menuOverlay?.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        toggleMenuBtn?.addEventListener('click', () => {
            if (categoryMenu?.classList.contains('-translate-y-full')) {
                openMenu();
            } else {
                closeMenu();
            }
        });
        closeMenuBtn?.addEventListener('click', closeMenu);
        menuOverlay?.addEventListener('click', closeMenu);

        // Navegación con scroll suave
        navLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const targetAttr = this.dataset.categoryTarget || this.getAttribute('href');
                const sectionId = targetAttr?.startsWith('#') ? targetAttr : `#${targetAttr}`;
                const target = document.querySelector(sectionId);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                    closeMenu();
                }
            });
        });

        // Resaltar link activo según la categoría visible
        const sectionObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.dataset.categoryId;
                    navLinks.forEach(link => {
                        link.classList.toggle('active', link.dataset.categoryTarget === id);
                    });
                }
            });
        }, { threshold: 0.3, rootMargin: '-10% 0px -55% 0px' });

        document.querySelectorAll('.category-section').forEach(section => {
            sectionObserver.observe(section);
        });

        // Animar tarjetas al aparecer
        const cardObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    cardObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        document.querySelectorAll('.dish-card').forEach(card => cardObserver.observe(card));

        const menuPopups = @json($popups ?? []);
        const now = new Date();
        const today = now.getDay();

        menuPopups.forEach(popup => {
            const start = popup.start_date ? new Date(popup.start_date) : null;
            const end = popup.end_date ? new Date(popup.end_date) : null;
            const repeatDays = popup.repeat_days ? popup.repeat_days.split(',').map(day => parseInt(day, 10)) : [];
            const withinDates = (!start || now >= start) && (!end || now <= end);
            const matchesDay = repeatDays.length === 0 || repeatDays.includes(today);

            if (popup.active && popup.view === 'menu' && withinDates && matchesDay) {
                showMenuPopup(popup);
            }
        });

        if (orderMode) {
            setupOrderMode();
        }
    });

    function groupExtras(extras = []) {
        return extras.reduce((groups, extra) => {
            const groupName = extra.group_name || extra.name || 'Opciones';
            if (!groups[groupName]) {
                groups[groupName] = {
                    kind: extra.kind || 'modifier',
                    required: !!extra.group_required || !!extra.min_select,
                    maxSelect: extra.max_select || null,
                    minSelect: extra.min_select || null,
                    options: [],
                };
            }
            if (!groups[groupName].kind && extra.kind) {
                groups[groupName].kind = extra.kind;
            }
            if (extra.group_required || extra.min_select) {
                groups[groupName].required = true;
            }
            if (extra.max_select) {
                groups[groupName].maxSelect = extra.max_select;
            }
            if (extra.min_select) {
                groups[groupName].minSelect = extra.min_select;
            }
            groups[groupName].options.push(extra);
            return groups;
        }, {});
    }

    function hasRequiredExtras(extras = []) {
        const groups = groupExtras(extras);
        return Object.values(groups).some(group => group.required || (group.minSelect && group.minSelect > 0));
    }

    function getItemCard(type, id) {
        const prefix = type === 'dish' ? 'dish' : type;
        return document.getElementById(`${prefix}${id}`);
    }

    function addUpsellToCart(upsell) {
        let taxes = [];
        const target = getItemCard(upsell.type, upsell.id);
        if (target?.dataset?.taxes) {
            try {
                taxes = JSON.parse(target.dataset.taxes);
            } catch (error) {
                taxes = [];
            }
        }
        cartItems.push({
            type: upsell.type,
            id: Number(upsell.id),
            name: upsell.name,
            price: Number(upsell.price || 0),
            quantity: 1,
            notes: null,
            extras: [],
            taxes,
        });

        updateCartCount();
        renderCart();
    }

    // Función para abrir modal con datos del plato
    function openDishModal(el) {
        const name = el.dataset.name;
        const description = el.dataset.description;
        const price = el.dataset.price;
        const fallbackImage = "{{ asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}";
        const image = el.dataset.image && !el.dataset.image.endsWith('/storage/') ? el.dataset.image : fallbackImage;
        const wines = el.dataset.wines;
        const pairings = el.dataset.recommended;
        const upsells = el.dataset.upsells ? JSON.parse(el.dataset.upsells) : [];
        const extras = el.dataset.extras ? JSON.parse(el.dataset.extras) : [];

        document.getElementById('modalTitle').textContent = name;
        document.getElementById('modalDescription').textContent = description;
        document.getElementById('modalPrice').textContent = price;
        document.getElementById('modalImage').src = image;

        const wineList = document.getElementById('wineList');
        const pairingList = document.getElementById('pairingList');
        const upsellList = document.getElementById('upsellList');

        wineList.innerHTML = '';
        pairingList.innerHTML = '';
        upsellList.innerHTML = '';

        if (orderMode) {
            document.getElementById('modalWines').classList.add('hidden');
            document.getElementById('modalPairings').classList.add('hidden');
        } else {
            if (wines && wines.trim() !== '') {
                wines.split('|').forEach(token => {
                    const [wineId, wineName] = token.split('::');
                    const li = document.createElement('li');
                    const link = document.createElement('a');
                    link.textContent = (wineName || token).trim();
                    link.href = '{{ route('coffee.index') }}#drink' + (wineId || '').trim();
                    link.className = 'text-amber-500 hover:underline';
                    li.appendChild(link);
                    wineList.appendChild(li);
                });
                document.getElementById('modalWines').classList.remove('hidden');
            } else {
                document.getElementById('modalWines').classList.add('hidden');
            }

            if (pairings && pairings.trim() !== '') {
                pairings.split('|').forEach(token => {
                    const [dishId, dishName] = token.split('::');
                    const li = document.createElement('li');
                    const link = document.createElement('a');
                    link.textContent = (dishName || token).trim();
                    link.href = '#dish' + (dishId || '').trim();
                    link.className = 'text-amber-500 hover:underline';
                    li.appendChild(link);
                    pairingList.appendChild(li);
                });
                document.getElementById('modalPairings').classList.remove('hidden');
            } else {
                document.getElementById('modalPairings').classList.add('hidden');
            }
        }

        const resolveUpsellHref = (upsell) => {
            if (upsell.type === 'dish') {
                return '#dish' + upsell.id;
            }
            if (upsell.type === 'wine') {
                return '{{ route('coffee.index') }}#drink' + upsell.id;
            }
            if (upsell.type === 'cocktail') {
                return '{{ route('cocktails.index') }}#drink' + upsell.id;
            }
            return '#';
        };

        let filteredUpsells = upsells;
        if (!orderMode) {
            const seenUpsells = new Set();
            if (wines && wines.trim() !== '') {
                wines.split('|').forEach(token => {
                    const [wineId] = token.split('::');
                    if (wineId) {
                        seenUpsells.add(`wine-${wineId.trim()}`);
                    }
                });
            }
            if (pairings && pairings.trim() !== '') {
                pairings.split('|').forEach(token => {
                    const [dishId] = token.split('::');
                    if (dishId) {
                        seenUpsells.add(`dish-${dishId.trim()}`);
                    }
                });
            }
            filteredUpsells = upsells.filter(upsell => {
                const key = `${upsell.type}-${upsell.id}`;
                return !seenUpsells.has(key);
            });
        }

        if (filteredUpsells.length) {
            if (orderMode) {
                filteredUpsells.forEach(upsell => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    const priceValue = Number(upsell.price || 0);
                    button.textContent = priceValue
                        ? `+ ${upsell.name} · $${priceValue.toFixed(2)}`
                        : `+ ${upsell.name}`;
                    button.className = 'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border bg-white/80 text-slate-800 border-slate-200 hover:border-amber-400 hover:text-amber-600';
                    button.addEventListener('click', () => {
                        const target = getItemCard(upsell.type, upsell.id);
                        if (target) {
                            const targetExtras = target.dataset.extras
                                ? JSON.parse(target.dataset.extras)
                                : [];
                            if (hasRequiredExtras(targetExtras)) {
                                openDishModal(target);
                                return;
                            }
                        }
                        addUpsellToCart(upsell);
                    });
                    upsellList.appendChild(button);
                });
            } else {
                filteredUpsells.forEach(upsell => {
                    const link = document.createElement('a');
                    const priceValue = Number(upsell.price || 0);
                    link.textContent = priceValue
                        ? `${upsell.name} · $${priceValue.toFixed(2)}`
                        : `${upsell.name}`;
                    link.href = resolveUpsellHref(upsell);
                    link.className = 'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border bg-white/80 text-slate-800 border-slate-200 hover:border-amber-400 hover:text-amber-600';
                    upsellList.appendChild(link);
                });
            }
            document.getElementById('modalUpsells').classList.remove('hidden');
        } else {
            document.getElementById('modalUpsells').classList.add('hidden');
        }

        const extrasSection = document.getElementById('modalExtras');
        const extrasList = document.getElementById('extrasList');
        extrasList.innerHTML = '';
        const groupedExtras = groupExtras(extras);
        const groupEntries = Object.entries(groupedExtras);
        if (groupEntries.length) {
            if (!orderMode) {
                const normalizeKind = (group) => group.kind || 'modifier';
                const modifierGroups = groupEntries.filter(([, group]) => normalizeKind(group) === 'modifier');
                const extraGroups = groupEntries.filter(([, group]) => normalizeKind(group) !== 'modifier');

                const renderGroupSection = (entries, sectionLabel, sectionClass) => {
                    if (!entries.length) {
                        return;
                    }

                    const sectionTitle = document.createElement('li');
                    sectionTitle.className = `text-xs uppercase tracking-[0.2em] ${sectionClass}`;
                    sectionTitle.textContent = sectionLabel;
                    extrasList.appendChild(sectionTitle);

                    entries.forEach(([groupName, group]) => {
                        const onlyOption = group.options.length === 1 ? group.options[0] : null;
                        const optionName = onlyOption?.name || '';
                        const showGroupTitle =
                            group.options.length > 1 || (optionName && optionName !== groupName);

                        const groupWrapper = document.createElement('li');
                        groupWrapper.className = 'flex flex-col gap-2 border border-slate-200/60 rounded-xl px-3 py-2 bg-white/40';
                        if (showGroupTitle) {
                            const groupTitle = document.createElement('p');
                            groupTitle.className = 'text-xs uppercase tracking-[0.2em] text-slate-500';
                            groupTitle.textContent = groupName;
                            groupWrapper.appendChild(groupTitle);
                        }

                        const optionsWrapper = document.createElement('div');
                        optionsWrapper.className = 'flex flex-col gap-2';
                        group.options.forEach(extra => {
                            const row = document.createElement('div');
                            row.className = 'flex items-center justify-between gap-3 text-sm font-semibold text-slate-800';
                            const nameSpan = document.createElement('span');
                            nameSpan.textContent = extra.name || 'Opción';
                            const priceSpan = document.createElement('span');
                            const priceValue = parseFloat(extra.price ?? 0);
                            priceSpan.textContent = priceValue ? `$${priceValue.toFixed(2)}` : '';
                            row.appendChild(nameSpan);
                            row.appendChild(priceSpan);
                            optionsWrapper.appendChild(row);

                            if (extra.description) {
                                const desc = document.createElement('p');
                                desc.className = 'text-xs text-slate-600';
                                desc.textContent = extra.description;
                                optionsWrapper.appendChild(desc);
                            }
                        });

                        groupWrapper.appendChild(optionsWrapper);
                        extrasList.appendChild(groupWrapper);
                    });
                };

                renderGroupSection(modifierGroups, 'Opciones a escoger', 'text-slate-500');
                renderGroupSection(extraGroups, 'Extras (costo adicional)', 'text-amber-600');
            } else {
                let groupIndex = 0;
                groupEntries.forEach(([groupName, group]) => {
                    const groupWrapper = document.createElement('li');
                    groupWrapper.className = 'flex flex-col gap-2 border border-slate-200/60 rounded-xl px-3 py-2 bg-white/40';
                    const headerRow = document.createElement('div');
                    headerRow.className = 'flex items-center justify-between gap-2';
                    const groupTitle = document.createElement('p');
                    groupTitle.className = 'text-sm font-semibold text-slate-700';
                    groupTitle.textContent = groupName;
                    headerRow.appendChild(groupTitle);
                    const maxSelect = group.maxSelect || (group.kind === 'modifier' ? 1 : null);
                    if (group.required || maxSelect) {
                        const badge = document.createElement('span');
                        badge.className = 'text-[10px] uppercase tracking-[0.2em] text-amber-600 font-semibold';
                        if (group.required && maxSelect) {
                            badge.textContent = `Requerido · Max ${maxSelect}`;
                        } else if (group.required) {
                            badge.textContent = 'Requerido';
                        } else if (maxSelect) {
                            badge.textContent = `Max ${maxSelect}`;
                        }
                        headerRow.appendChild(badge);
                    }
                    groupWrapper.appendChild(headerRow);

                    const optionsWrapper = document.createElement('div');
                    optionsWrapper.className = 'flex flex-col gap-2';
                    const isModifier = maxSelect === 1;
                    const inputName = `extra-group-${groupIndex}`;
                    if (group.required || maxSelect) {
                        const helper = document.createElement('p');
                        helper.className = 'text-xs text-slate-500';
                        if (group.required && maxSelect) {
                            helper.textContent = `Requiere minimo 1 · Max ${maxSelect}`;
                        } else if (group.required) {
                            helper.textContent = 'Requiere minimo 1';
                        } else if (maxSelect) {
                            helper.textContent = `Max ${maxSelect}`;
                        }
                        groupWrapper.appendChild(helper);
                    }
                    group.options.forEach(extra => {
                        const row = document.createElement('label');
                        row.className = 'flex items-center justify-between gap-3 text-sm font-semibold';
                        const left = document.createElement('span');
                        left.className = 'flex items-center gap-2';

                        const input = document.createElement('input');
                        input.type = isModifier ? 'radio' : 'checkbox';
                        if (isModifier) {
                            input.name = inputName;
                        }
                        input.className = 'h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-500';
                        input.dataset.extraId = extra.id || '';
                        input.dataset.extraName = extra.name || 'Opción';
                        input.dataset.extraPrice = extra.price || '0';
                        input.dataset.groupName = groupName;
                        input.dataset.extraKind = group.kind || 'modifier';
                        input.dataset.groupRequired = group.required ? '1' : '0';
                        input.dataset.maxSelect = maxSelect ? String(maxSelect) : '';
                        left.appendChild(input);

                        if (!isModifier && maxSelect) {
                            input.addEventListener('change', () => {
                                if (!input.checked) return;
                                const selected = extrasList.querySelectorAll(`input[data-group-name=\"${groupName}\"]:checked`);
                                if (selected.length > maxSelect) {
                                    input.checked = false;
                                    alert(`Solo puedes seleccionar ${maxSelect} opcion(es) en ${groupName}.`);
                                }
                            });
                        }

                        const nameSpan = document.createElement('span');
                        nameSpan.textContent = extra.name || 'Opción';
                        left.appendChild(nameSpan);

                        const priceSpan = document.createElement('span');
                        const priceValue = parseFloat(extra.price ?? 0);
                        priceSpan.textContent = priceValue ? `$${priceValue.toFixed(2)}` : '';

                        row.appendChild(left);
                        row.appendChild(priceSpan);
                        optionsWrapper.appendChild(row);

                        if (extra.description) {
                            const desc = document.createElement('p');
                            desc.className = 'text-xs text-slate-600';
                            desc.textContent = extra.description;
                            optionsWrapper.appendChild(desc);
                        }
                    });

                    const canSelectAll = !maxSelect || maxSelect >= group.options.length;
                    if (!isModifier && canSelectAll && group.options.length > 1) {
                        const toggleButton = document.createElement('button');
                        toggleButton.type = 'button';
                        toggleButton.className = 'text-xs font-semibold text-amber-600';

                        const updateToggleLabel = () => {
                            const inputs = optionsWrapper.querySelectorAll('input');
                            const selectedCount = [...inputs].filter(input => input.checked).length;
                            const limit = inputs.length;
                            if (selectedCount >= limit) {
                                toggleButton.textContent = 'Quitar';
                            } else {
                                toggleButton.textContent = 'Seleccionar todo';
                            }
                        };

                        toggleButton.addEventListener('click', () => {
                            const inputs = optionsWrapper.querySelectorAll('input');
                            const selectedCount = [...inputs].filter(input => input.checked).length;
                            if (selectedCount >= inputs.length) {
                                inputs.forEach(input => {
                                    input.checked = false;
                                });
                            } else {
                                inputs.forEach(input => {
                                    input.checked = true;
                                });
                            }
                            updateToggleLabel();
                        });

                        optionsWrapper.querySelectorAll('input').forEach(input => {
                            input.addEventListener('change', updateToggleLabel);
                        });

                        updateToggleLabel();
                        headerRow.appendChild(toggleButton);
                    }

                    groupWrapper.appendChild(optionsWrapper);
                    extrasList.appendChild(groupWrapper);
                    groupIndex += 1;
                });
            }
            extrasSection.classList.remove('hidden');
        } else {
            extrasSection.classList.add('hidden');
        }

        if (orderMode) {
            activeItem = {
                type: el.dataset.itemType,
                id: Number(el.dataset.itemId),
                name,
                price: Number(String(price).replace(/[^0-9.-]+/g, '')),
                extras,
                taxes: el.dataset.taxes ? JSON.parse(el.dataset.taxes) : [],
            };
            setQuantity(1);
            const notesInput = document.getElementById('itemNotes');
            if (notesInput) {
                notesInput.value = '';
            }
        }

        // Mostrar el modal con Flowbite
        const modalEl = document.getElementById('dishDetailsModal');
        if (window.dishModalInstance) {
            window.dishModalInstance.show();
        } else {
            window.dishModalInstance = new Modal(modalEl);
            window.dishModalInstance.show();
        }
    }

    function closeDishModal() {
        const activeElement = document.activeElement;
        if (activeElement && typeof activeElement.blur === 'function') {
            activeElement.blur();
        }
        if (window.dishModalInstance) {
            window.dishModalInstance.hide();
        }
    }

    function setupOrderMode() {
        const openCartButton = document.getElementById('openCartButton');
        const closeCartButton = document.getElementById('closeCartButton');
        const clearCartButton = document.getElementById('clearCartButton');
        const sendOrderButton = document.getElementById('sendOrderButton');
        const qtyMinus = document.getElementById('qtyMinus');
        const qtyPlus = document.getElementById('qtyPlus');
        const addToCartButton = document.getElementById('addToCartButton');

        openCartButton?.addEventListener('click', openCart);
        closeCartButton?.addEventListener('click', closeCart);
        clearCartButton?.addEventListener('click', () => {
            cartItems = [];
            renderCart();
            updateCartCount();
        });
        sendOrderButton?.addEventListener('click', sendOrder);

        qtyMinus?.addEventListener('click', () => setQuantity(currentQty - 1));
        qtyPlus?.addEventListener('click', () => setQuantity(currentQty + 1));

        addToCartButton?.addEventListener('click', () => {
            if (!activeItem) return;
            if (!validateRequiredGroups(activeItem.extras || [])) return;
            const notes = document.getElementById('itemNotes')?.value?.trim() || null;
            const extras = getSelectedExtras();
            const taxes = activeItem.taxes || [];

            cartItems.push({
                type: activeItem.type,
                id: activeItem.id,
                name: activeItem.name,
                price: activeItem.price,
                quantity: currentQty,
                notes,
                extras,
                taxes,
            });

            updateCartCount();
            renderCart();
            closeDishModal();
        });
    }

    function setQuantity(value) {
        currentQty = Math.max(1, Math.min(value, 99));
        const qtyValue = document.getElementById('qtyValue');
        if (qtyValue) {
            qtyValue.textContent = String(currentQty);
        }
    }

    function getSelectedExtras() {
        const selected = [];
        const inputs = document.querySelectorAll('#extrasList input:checked');
        inputs.forEach(input => {
            const extraId = Number(input.dataset.extraId);
            if (!extraId) return;
            selected.push({
                id: extraId,
                name: input.dataset.extraName || 'Opción',
                price: Number(input.dataset.extraPrice || 0),
                quantity: 1,
                group_name: input.dataset.groupName || null,
                kind: input.dataset.extraKind || null,
            });
        });
        return selected;
    }

    function validateRequiredGroups(extras) {
        const grouped = groupExtras(extras || []);
        const selections = {};
        const inputs = document.querySelectorAll('#extrasList input:checked');
        inputs.forEach(input => {
            const groupName = input.dataset.groupName || 'Opciones';
            selections[groupName] = (selections[groupName] || 0) + 1;
        });

        const missing = Object.entries(grouped)
            .filter(([, group]) => group.required)
            .filter(([groupName]) => !selections[groupName])
            .map(([groupName]) => groupName);

        if (missing.length) {
            alert(`Selecciona las opciones requeridas: ${missing.join(', ')}`);
            return false;
        }

        return true;
    }

    function updateCartCount() {
        const cartCount = document.getElementById('cartCount');
        if (!cartCount) return;
        const total = cartItems.reduce((sum, item) => sum + (item.quantity || 0), 0);
        cartCount.textContent = String(total);
    }

    function openCart() {
        const modalEl = document.getElementById('orderCartModal');
        if (!modalEl) return;
        if (!cartModalInstance) {
            cartModalInstance = new Modal(modalEl);
        }
        renderCart();
        cartModalInstance.show();
    }

    function closeCart() {
        const activeElement = document.activeElement;
        if (activeElement && typeof activeElement.blur === 'function') {
            activeElement.blur();
        }
        if (cartModalInstance) {
            cartModalInstance.hide();
        }
    }

    function renderCart() {
        const container = document.getElementById('cartItems');
        if (!container) return;
        container.innerHTML = '';

        if (!cartItems.length) {
            const empty = document.createElement('p');
            empty.className = 'text-sm text-slate-500';
            empty.textContent = 'Tu pedido está vacío.';
            container.appendChild(empty);
            renderCartTotals();
            return;
        }

        cartItems.forEach((item, index) => {
            const card = document.createElement('div');
            card.className = 'rounded-xl border border-slate-200 px-3 py-2';

            const header = document.createElement('div');
            header.className = 'flex items-center justify-between gap-2';

            const name = document.createElement('div');
            name.className = 'text-sm font-semibold text-slate-900';
            name.textContent = `${item.name}`;

            const controls = document.createElement('div');
            controls.className = 'flex items-center gap-2';

            const qtyWrapper = document.createElement('div');
            qtyWrapper.className = 'flex items-center gap-2 rounded-full border border-slate-200 px-2 py-1 text-xs text-slate-700';

            const qtyMinus = document.createElement('button');
            qtyMinus.type = 'button';
            qtyMinus.textContent = '-';
            qtyMinus.className = 'h-6 w-6 rounded-full border border-slate-300 text-slate-600';
            qtyMinus.addEventListener('click', () => {
                item.quantity = Math.max(1, (item.quantity || 1) - 1);
                renderCart();
                updateCartCount();
            });

            const qtyValue = document.createElement('span');
            qtyValue.className = 'min-w-[1.5rem] text-center font-semibold text-slate-800';
            qtyValue.textContent = String(item.quantity || 1);

            const qtyPlus = document.createElement('button');
            qtyPlus.type = 'button';
            qtyPlus.textContent = '+';
            qtyPlus.className = 'h-6 w-6 rounded-full border border-slate-300 text-slate-600';
            qtyPlus.addEventListener('click', () => {
                item.quantity = Math.min(99, (item.quantity || 1) + 1);
                renderCart();
                updateCartCount();
            });

            qtyWrapper.appendChild(qtyMinus);
            qtyWrapper.appendChild(qtyValue);
            qtyWrapper.appendChild(qtyPlus);

            const removeButton = document.createElement('button');
            removeButton.className = 'text-xs text-rose-500';
            removeButton.textContent = 'Quitar';
            removeButton.addEventListener('click', () => {
                cartItems.splice(index, 1);
                renderCart();
                updateCartCount();
            });

            controls.appendChild(qtyWrapper);
            controls.appendChild(removeButton);

            header.appendChild(name);
            header.appendChild(controls);
            card.appendChild(header);

            if (item.extras && item.extras.length) {
                const extrasList = document.createElement('div');
                extrasList.className = 'mt-2 text-xs text-slate-600';
                const grouped = item.extras.reduce((groups, extra) => {
                    const groupName = extra.group_name || 'Opciones';
                    if (!groups[groupName]) {
                        groups[groupName] = [];
                    }
                    groups[groupName].push(extra.name);
                    return groups;
                }, {});
                const details = Object.entries(grouped)
                    .map(([groupName, names]) => `${groupName}: ${names.join(', ')}`)
                    .join(' · ');
                extrasList.textContent = `Opciones: ${details}`;
                card.appendChild(extrasList);
            }

            if (item.notes) {
                const notes = document.createElement('div');
                notes.className = 'mt-2 text-xs text-slate-500';
                notes.textContent = `Nota: ${item.notes}`;
                card.appendChild(notes);
            }

            const priceRow = document.createElement('div');
            priceRow.className = 'mt-2 flex items-center justify-between text-xs text-slate-600';
            const unitPrice = calcItemUnitTotal(item);
            priceRow.innerHTML = `<span>$${unitPrice.toFixed(2)} x ${item.quantity || 1}</span><span class="font-semibold text-slate-800">$${(unitPrice * (item.quantity || 1)).toFixed(2)}</span>`;
            card.appendChild(priceRow);

            container.appendChild(card);
        });

        renderCartTotals();
    }

    function calcItemUnitTotal(item) {
        const basePrice = Number(item.price || 0);
        const extrasTotal = (item.extras || []).reduce((sum, extra) => {
            const extraPrice = Number(extra.price || 0);
            const extraQty = Number(extra.quantity || 1);
            return sum + (extraPrice * extraQty);
        }, 0);
        return basePrice + extrasTotal;
    }

    function calcItemTaxTotal(item) {
        const unitTotal = calcItemUnitTotal(item);
        const rateSum = (item.taxes || []).reduce((sum, tax) => sum + Number(tax.rate || 0), 0);
        const ratePercent = rateSum / 100;
        return unitTotal * ratePercent * (item.quantity || 1);
    }

    function renderCartTotals() {
        const subtotalEl = document.getElementById('cartSubtotal');
        const taxEl = document.getElementById('cartTaxTotal');
        const totalEl = document.getElementById('cartTotal');
        if (!subtotalEl || !taxEl || !totalEl) return;

        const subtotal = cartItems.reduce((sum, item) => {
            return sum + (calcItemUnitTotal(item) * (item.quantity || 1));
        }, 0);
        const taxTotal = cartItems.reduce((sum, item) => sum + calcItemTaxTotal(item), 0);
        const total = subtotal + taxTotal;

        subtotalEl.textContent = `$${subtotal.toFixed(2)}`;
        taxEl.textContent = `$${taxTotal.toFixed(2)}`;
        totalEl.textContent = `$${total.toFixed(2)}`;
    }

    async function sendOrder() {
        if (!cartItems.length) return;

        const sendOrderButton = document.getElementById('sendOrderButton');
        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content');
        const payload = {
            items: cartItems.map(item => ({
                type: item.type,
                id: item.id,
                quantity: item.quantity,
                notes: item.notes || null,
                extras: (item.extras || []).map(extra => ({
                    id: extra.id,
                    quantity: extra.quantity || 1,
                })),
            })),
        };

        try {
            if (orderChannel === 'online' && onlineOrdering && onlineOrdering.enabled === false) {
                alert(onlineOrdering.message || 'Por el momento no estamos tomando órdenes en línea.');
                return;
            }
            if (sendOrderButton) {
                sendOrderButton.disabled = true;
                sendOrderButton.textContent = 'Enviando...';
            }

            if (orderChannel === 'online') {
                const customerName = document.getElementById('onlineCustomerName')?.value?.trim();
                const customerEmail = document.getElementById('onlineCustomerEmail')?.value?.trim();
                const customerPhone = document.getElementById('onlineCustomerPhone')?.value?.trim();
                const pickupAt = document.getElementById('onlinePickupAt')?.value;
                const orderNotes = document.getElementById('onlineOrderNotes')?.value?.trim();

                if (!customerName) {
                    alert('Indica el nombre para la orden.');
                    return;
                }
                if (!pickupAt) {
                    alert('Selecciona la hora de recogido.');
                    return;
                }

                const response = await fetch('/ordenar/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                    },
                    body: JSON.stringify({
                        ...payload,
                        customer_name: customerName,
                        customer_email: customerEmail || null,
                        customer_phone: customerPhone || null,
                        pickup_at: pickupAt,
                        notes: orderNotes || null,
                    }),
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    alert(data.message || 'No se pudo crear el checkout.');
                    return;
                }

                if (data.checkout_page) {
                    window.location.href = data.checkout_page;
                    return;
                }
                if (data.checkout_url) {
                    window.location.href = data.checkout_url;
                    return;
                }

                alert('Checkout creado.');
                return;
            }

            if (!orderToken) {
                alert('No se encontró la mesa.');
                return;
            }

            const response = await fetch(`/mesa/${orderToken}/orders`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                alert(data.message || 'No se pudo enviar la orden.');
                return;
            }

            cartItems = [];
            renderCart();
            updateCartCount();
            closeCart();
            alert(data.message || 'Orden enviada.');
        } catch (error) {
            alert('No se pudo enviar la orden.');
        } finally {
            if (sendOrderButton) {
                sendOrderButton.disabled = false;
                sendOrderButton.textContent = orderChannel === 'online'
                    ? 'Pagar y enviar'
                    : 'Enviar orden al mesero';
            }
        }
    }

    function showMenuPopup(popup) {
        const modalEl = document.getElementById('menuPopupModal');
        if (!modalEl) return;
        if (!menuPopupInstance) {
            menuPopupInstance = new Modal(modalEl, { closable: true });
        }
        const basePath = '{{ asset('storage') }}/';
        document.getElementById('menuPopupTitle').textContent = popup.title || '';
        document.getElementById('menuPopupImage').src = popup.image ? basePath + popup.image : '';
        menuPopupInstance.show();
    }

    function closeMenuPopup() {
        if (menuPopupInstance) {
            menuPopupInstance.hide();
        }
    }

    function initSubcategoryTabs() {
        const tabRows = document.querySelectorAll('[data-category-tabs]');
        tabRows.forEach((row) => {
            const categoryId = row.dataset.categoryTabs;
            const tabs = row.querySelectorAll('[data-subcategory-tab]');
            const panels = document.querySelectorAll(`[data-category-panel="${categoryId}"]`);
            if (!tabs.length || !panels.length) return;

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    const target = tab.dataset.subcategoryTab;
                    tabs.forEach((btn) => btn.classList.remove('active'));
                    tab.classList.add('active');
                    panels.forEach((panel) => {
                        if (panel.dataset.subcategoryPanel === target) {
                            panel.classList.remove('hidden');
                        } else {
                            panel.classList.add('hidden');
                        }
                    });
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', initSubcategoryTabs);
</script>

</body>
</html>
