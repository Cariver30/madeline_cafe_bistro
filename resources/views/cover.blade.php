<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @php
        $appName = config('app.name', 'Restaurant');
        $heroTitle = trim($settings?->cover_hero_title ?? '');
        $heroParagraph = trim($settings?->cover_hero_paragraph ?? '');
        $defaultTitle = $heroTitle !== ''
            ? "{$heroTitle} · {$appName}"
            : "{$appName} · Menú y experiencias";
        $defaultDescription = $heroParagraph !== ''
            ? $heroParagraph
            : "Descubre el menú, bebidas y experiencias de {$appName}.";
        $seoTitle = trim($settings?->seo_title ?? '') !== '' ? $settings->seo_title : $defaultTitle;
        $seoDescription = trim($settings?->seo_description ?? '') !== '' ? $settings->seo_description : $defaultDescription;
        $seoImage = $settings?->seo_image
            ? asset('storage/' . $settings->seo_image)
            : ($settings?->logo
                ? asset('storage/' . $settings->logo)
                : asset('storage/default-logo.png'));
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

    @php
        if (!function_exists('cover_card_color')) {
            function cover_card_color(?string $hex, $opacity)
            {
                $opacity = is_numeric($opacity) ? max(0, min(1, $opacity)) : 0.85;
                if (!$hex) {
                    return "rgba(0,0,0,{$opacity})";
                }
                $hex = ltrim($hex, '#');
                if (strlen($hex) === 3) {
                    $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                }
                if (strlen($hex) !== 6) {
                    return "rgba(0,0,0,{$opacity})";
                }
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                return "rgba({$r},{$g},{$b},{$opacity})";
            }
        }
        $coverBaseColor = $settings->text_color_cover ?? '#ffffff';
        $coverBodyColor = $settings->text_color_cover_secondary ?? $coverBaseColor;
        $coverCardBackground = cover_card_color($settings->card_bg_color_cover ?? null, $settings->card_opacity_cover ?? 0.85);
        $pointsPerVisit = $settings->loyalty_points_per_visit ?? 10;
    @endphp
    <style>
        :root {
            --accent-color: {{ $settings->button_color_cover ?? '#FF5722' }};
            --cover-heading-color: {{ $coverBaseColor }};
            --cover-body-color: {{ $coverBodyColor }};
            --cover-body-soft: {{ cover_card_color($coverBodyColor, 0.65) }};
        }
        body {
            font-family: {{ $settings->font_family_cover ?? 'ui-sans-serif' }};
            @if($settings && $settings->background_image_cover)
                background: none;
            @endif
            background-size: cover;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: -1;
            width: 100vw;
            height: 100vh;
            @if($settings && $settings->background_image_cover)
                background: url('{{ asset('storage/' . $settings->background_image_cover) }}') no-repeat center center;
                background-size: cover;
            @endif
        }
        .cover-theme {
            color: var(--cover-body-color);
        }
        .cover-text-primary {
            color: var(--cover-heading-color);
        }
        .cover-text-muted {
            color: var(--cover-body-color);
        }
        .cover-text-soft {
            color: var(--cover-body-soft);
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .vip-button {
            position: relative;
            width: 12rem;
            height: 3rem;
            border-radius: 9999px;
            font-weight: 600;
            color: var(--vip-button-text, #fff);
            background: var(--vip-button-bg, var(--accent-color));
            transition: transform .2s ease, box-shadow .2s ease;
            animation: vip-glow 1.5s infinite;
            overflow: hidden;
        }
        .vip-button::after {
            content: '';
            position: absolute;
            inset: 4px;
            border-radius: 9999px;
            border: 2px dashed currentColor;
            opacity: 0.65;
            animation: vip-blink 2s linear infinite;
            pointer-events: none;
        }
        .vip-button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 18px rgba(255,255,255,0.35);
        }
        @keyframes vip-glow {
            0%, 100% { box-shadow: 0 0 12px rgba(255,255,255,0.15); }
            50% { box-shadow: 0 0 20px rgba(255,255,255,0.45); }
        }
        @keyframes vip-blink {
            0% { opacity: 0.25; }
            50% { opacity: 1; }
            100% { opacity: 0.25; }
        }
    </style>
</head>
<body class="relative min-h-screen bg-black/50 flex flex-col items-center cover-theme">

    <header class="w-full py-6 flex justify-center z-30">
        @if($settings?->logo)
            <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo del Restaurante" class="w-52 max-w-xs mx-auto drop-shadow-lg">
        @else
            <div class="rounded-full border border-white/30 bg-black/40 px-6 py-4 text-xl font-semibold uppercase tracking-[0.2em] text-white shadow-lg">
                {{ config('app.name', 'Restaurant') }}
            </div>
        @endif
    </header>

    <!-- Contenedor central -->
    <main class="z-20 w-full px-4 pb-16">
        @if(session('notification_success'))
            <div id="subscriptionStatus" class="max-w-md mx-auto mb-6 bg-emerald-500/20 border border-emerald-400/30 rounded-2xl px-4 py-3 text-sm text-emerald-100">
                {{ session('notification_success') }}
            </div>
        @endif

        <div class="max-w-6xl mx-auto space-y-10" style="color: var(--cover-body-color);">
            <section class="rounded-3xl p-8 backdrop-blur space-y-8 border border-white/10" style="background-color: {{ $coverCardBackground }};">
                <div class="flex flex-col lg:flex-row gap-8">
                    @php
                        $appName = config('app.name', 'Restaurant');
                        $heroKicker = trim($settings->cover_hero_kicker ?? '') ?: 'Sabores · momentos · experiencias';
                        $heroTitle = trim($settings->cover_hero_title ?? '') ?: "Bienvenido a {$appName}. Aqui el visitante decide rapido a que experiencia ir.";
                        $heroParagraph = trim($settings->cover_hero_paragraph ?? '') ?: 'Todos los colores, tipografias y textos provienen del panel de configuraciones. Ajusta alla y veras los cambios inmediatamente.';
                        $locationText = trim($settings->cover_location_text ?? '') ?: $appName;
                    @endphp
                    <div class="flex-1 space-y-4">
                        <p class="text-amber-300 uppercase tracking-[0.45em] text-xs">{{ $heroKicker }}</p>
                        <h1 class="text-4xl lg:text-5xl font-semibold leading-tight cover-text-primary" style="font-family: {{ $settings->font_family_cover ?? 'ui-sans-serif' }};">
                            {{ $heroTitle }}
                        </h1>
                        <p class="cover-text-muted text-lg">{{ $heroParagraph }}</p>
                    </div>
                    <div class="w-full max-w-md space-y-4">
                        <article class="space-y-4 bg-white/5 border border-white/10 rounded-2xl p-5">
                            <div>
                                <p class="text-xs uppercase tracking-[0.35em] cover-text-soft mb-1">Horarios</p>
                                <p class="cover-text-muted whitespace-pre-line text-sm">{{ $settings->business_hours ?? "Viernes y sábado 12pm – 10pm\nDomingo 12pm – 8pm" }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-sm cover-text-muted">
                                <div class="rounded-xl border border-white/15 p-3">
                                    <p class="cover-text-soft uppercase text-xs tracking-[0.3em] mb-1">Teléfono</p>
                                    <p>{{ $settings->phone_number ?? '787-000-0000' }}</p>
                                </div>
                                <div class="rounded-xl border border-white/15 p-3">
                                    <p class="cover-text-soft uppercase text-xs tracking-[0.3em] mb-1">Ubicación</p>
                                    <p>{{ $locationText }}</p>
                                </div>
                            </div>
                        </article>
                        <article class="bg-white/5 border border-white/10 rounded-2xl p-5 flex flex-col gap-3">
                            <div class="flex items-start gap-3">
                                <svg viewBox="0 0 64 64" class="w-10 h-10 text-amber-200">
                                    <circle cx="32" cy="32" r="30" stroke="currentColor" stroke-width="2" fill="none"></circle>
                                    <path d="M18 32h28M32 18v28" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                </svg>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.35em] cover-text-soft mb-1">Fidelidad</p>
                                    <h3 class="text-xl font-semibold cover-text-primary">Suma {{ $pointsPerVisit }} pts por visita</h3>
                                    <p class="cover-text-muted text-sm">Escanea el QR del mesero y canjea tus visitas por flights privados, desayunos y experiencias.</p>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            @php
                $initialGroup = $featuredGroups->first();
                $featuredCardBgHex = $settings->featured_card_bg_color ?? '#0f172a';
                $featuredCardBg = cover_card_color($featuredCardBgHex, 0.65);
                $featuredCardText = $settings->featured_card_text_color ?? '#ffffff';
                $featuredMutedText = cover_card_color($featuredCardText, 0.75);
                $featuredBorderColor = cover_card_color($featuredCardText, 0.2);
                $featuredTabBgHex = $settings->featured_tab_bg_color ?? '#ffffff';
                $featuredTabBg = cover_card_color($featuredTabBgHex, 0.2);
                $featuredTabText = $settings->featured_tab_text_color ?? '#ffffff';
            @endphp

            <section class="rounded-3xl p-6 backdrop-blur space-y-6 border" style="background-color: {{ $featuredCardBg }}; color: {{ $featuredCardText }}; border-color: {{ $featuredBorderColor }}; font-family: {{ $settings->font_family_cover ?? 'inherit' }};">
                <div>
                    <p class="text-xs uppercase tracking-[0.4em]" style="color: {{ $featuredMutedText }};">Lo más vendido</p>
                    <h3 id="featuredHeadingTitle" class="text-3xl font-semibold">{{ $initialGroup['title'] ?? 'Selección del chef' }}</h3>
                    <p id="featuredHeadingSubtitle" class="text-sm" style="color: {{ $featuredMutedText }};">{{ $initialGroup['subtitle'] ?? 'Los favoritos de la semana.' }}</p>
                </div>
                @if($featuredGroups->isNotEmpty())
                    <div class="flex flex-wrap gap-3 text-sm">
                        @foreach($featuredGroups as $group)
                            <button class="px-4 py-2 rounded-full transition"
                                    data-featured-tab="{{ $group['slug'] }}"
                                    data-active-bg="{{ $featuredTabBg }}"
                                    data-inactive-bg="transparent"
                                    data-text="{{ $featuredCardText }}"
                                    data-border="{{ $featuredBorderColor }}"
                                    style="border: 1px solid {{ $featuredBorderColor }}; color: {{ $featuredCardText }}; background-color: {{ $loop->first ? $featuredTabBg : 'transparent' }};">
                                {{ $group['title'] }}
                            </button>
                        @endforeach
                    </div>
                    <div class="space-y-6">
                        <div>
                            <p id="featuredTag" class="text-xs uppercase tracking-[0.35em] mb-2" style="color: {{ $featuredMutedText }};">{{ $initialGroup['subtitle'] ?? '' }}</p>
                            <h3 id="featuredTitle" class="text-3xl font-semibold">{{ $initialGroup['title'] ?? 'Sin datos' }}</h3>
                            <p id="featuredDescription" class="mt-2" style="color: {{ $featuredMutedText }};">{{ $initialGroup['source_label'] ?? '' }}</p>
                        </div>
                        <div id="featuredItems" class="space-y-4">
                            @forelse($initialGroup['items'] ?? [] as $item)
                                <a href="{{ $item['link'] ?? '#' }}" class="flex items-start justify-between gap-4 pb-3 group" style="color: {{ $featuredCardText }}; border-bottom: 1px solid {{ $featuredBorderColor }};">
                                    <div class="flex items-start gap-3">
                                        @if(!empty($item['image']))
                                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="w-14 h-14 rounded-xl object-cover border" style="border-color: {{ $featuredBorderColor }};">
                                        @else
                                            <div class="w-14 h-14 rounded-xl flex items-center justify-center text-lg" style="border:1px solid {{ $featuredBorderColor }};">☆</div>
                                        @endif
                                        <div>
                                            <p class="text-lg font-semibold">{{ $item['title'] }}</p>
                                            <p class="text-sm line-clamp-2" style="color: {{ $featuredMutedText }};">{{ $item['subtitle'] }}</p>
                                        </div>
                                    </div>
                                    @if(!empty($item['price']))
                                        <span class="font-semibold" style="color: {{ $settings->button_color_cover ?? $featuredCardText }};">${{ number_format($item['price'], 2) }}</span>
                                    @endif
                                </a>
                            @empty
                                <p class="cover-text-soft text-sm">Marca platos o bebidas como destacados dentro de la categoría seleccionada.</p>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="p-6 border border-white/10 rounded-2xl bg-black/20">
                        <p class="text-sm">Estamos preparando nuevas experiencias. Vuelve pronto para descubrir los rituales de café y brunch más pedidos.</p>
                    </div>
                @endif

            </section>

            <section class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-10">
                @php
                    $ctaLabel = function ($value, $default) {
                        if (is_null($value)) {
                            return $default;
                        }
                        $trimmed = trim($value);
                        return $trimmed === '' ? null : $trimmed;
                    };
                    $ctaCards = collect([
                        ['key' => 'menu', 'title' => $ctaLabel($settings->button_label_menu ?? null, 'Menú'), 'action' => url('/menu'), 'image' => $settings->cta_image_menu ? asset('storage/' . $settings->cta_image_menu) : null, 'visible' => $settings->show_cta_menu ?? true, 'type' => 'link'],
                        ['key' => 'online', 'title' => $ctaLabel($settings->button_label_online ?? null, 'Ordenar en línea'), 'action' => route('online.order.show'), 'image' => $settings->cta_image_online ? asset('storage/' . $settings->cta_image_online) : null, 'visible' => $settings->show_cta_online ?? true, 'type' => 'link'],
                        ['key' => 'cafe', 'title' => $ctaLabel($settings->button_label_wines ?? null, 'Bebidas'), 'action' => url('/coffee'), 'image' => $settings->cta_image_cafe ? asset('storage/' . $settings->cta_image_cafe) : null, 'visible' => $settings->show_cta_cafe ?? true, 'type' => 'link'],
                        ['key' => 'cocktails', 'title' => $ctaLabel($settings->button_label_cocktails ?? null, 'Cócteles'), 'action' => url('/cocktails'), 'image' => $settings->cta_image_cocktails ? asset('storage/' . $settings->cta_image_cocktails) : null, 'visible' => $settings->show_cta_cocktails ?? true, 'type' => 'link'],
                        ['key' => 'cantina', 'title' => $ctaLabel($settings->button_label_cantina ?? null, 'Cantina'), 'action' => url('/cantina'), 'image' => $settings->cta_image_cantina ? asset('storage/' . $settings->cta_image_cantina) : null, 'visible' => $settings->show_cta_cantina ?? true, 'type' => 'link'],
                        ['key' => 'specials', 'title' => $ctaLabel($settings->button_label_specials ?? null, 'Especiales'), 'action' => route('specials.index'), 'image' => $settings->cta_image_specials ? asset('storage/' . $settings->cta_image_specials) : null, 'visible' => $settings->show_cta_specials ?? true, 'type' => 'link'],
                        ['key' => 'events', 'title' => $ctaLabel($settings->button_label_events ?? null, 'Eventos especiales'), 'action' => route('experiences.index'), 'image' => $settings->cta_image_events ? asset('storage/' . $settings->cta_image_events) : null, 'visible' => $settings->show_cta_events ?? true, 'type' => 'link'],
                        ['key' => 'reservations', 'title' => $ctaLabel($settings->button_label_reservations ?? null, 'Reservas'), 'action' => route('reservations.app'), 'image' => $settings->cta_image_reservations ? asset('storage/' . $settings->cta_image_reservations) : null, 'visible' => $settings->show_cta_reservations ?? true, 'type' => 'link'],
                        ['key' => 'vip', 'title' => $ctaLabel($settings->button_label_vip ?? null, 'Lista VIP'), 'action' => '#', 'image' => null, 'visible' => $settings->show_cta_vip ?? true, 'type' => 'vip'],
                    ])->filter(fn($card) => ($card['visible'] ?? true) && filled($card['title']))->map(function ($card) use ($settings, $coverCardBackground) {
                        $bg = $settings->{'cover_cta_'.$card['key'].'_bg_color'} ?? null;
                        $text = $settings->{'cover_cta_'.$card['key'].'_text_color'} ?? null;
                        $card['bg_color'] = $bg ?: $coverCardBackground;
                        $card['text_color'] = $text ?: 'var(--cover-body-color)';
                        return $card;
                    });

                    $targetActions = [
                        'menu' => ['type' => 'link', 'action' => url('/menu')],
                        'online' => ['type' => 'link', 'action' => route('online.order.show')],
                        'cafe' => ['type' => 'link', 'action' => url('/coffee')],
                        'cocktails' => ['type' => 'link', 'action' => url('/cocktails')],
                        'cantina' => ['type' => 'link', 'action' => url('/cantina')],
                        'specials' => ['type' => 'link', 'action' => route('specials.index')],
                        'events' => ['type' => 'link', 'action' => route('experiences.index')],
                        'reservations' => ['type' => 'link', 'action' => route('reservations.app')],
                        'vip' => ['type' => 'vip', 'action' => '#'],
                    ];

                    $storedTargets = $settings->cover_cta_targets ?? [];
                    if (is_string($storedTargets)) {
                        $decodedTargets = json_decode($storedTargets, true);
                        $storedTargets = is_array($decodedTargets) ? $decodedTargets : [];
                    }

                    $defaultOrder = ['menu', 'online', 'cafe', 'cocktails', 'cantina', 'specials', 'events', 'reservations', 'vip'];
                    $storedOrder = $settings->cover_cta_order ?? [];
                    if (is_string($storedOrder)) {
                        $decoded = json_decode($storedOrder, true);
                        $storedOrder = is_array($decoded) ? $decoded : [];
                    }
                    $storedOrder = array_values(array_unique(array_filter($storedOrder, fn($key) => in_array($key, $defaultOrder, true))));
                    $orderedKeys = array_values(array_merge($storedOrder, array_diff($defaultOrder, $storedOrder)));

                    $ctaCards = $ctaCards
                        ->map(function ($card) use ($storedTargets, $targetActions) {
                            $targetKey = $storedTargets[$card['key']] ?? $card['key'];
                            $target = $targetActions[$targetKey] ?? $targetActions[$card['key']] ?? null;
                            if ($target) {
                                $card['action'] = $target['action'];
                                $card['type'] = $target['type'];
                            }
                            return $card;
                        })
                        ->sortBy(fn($card) => array_search($card['key'], $orderedKeys, true))
                        ->values();
                @endphp
                @foreach($ctaCards as $card)
                    <article class="border border-white/10 rounded-2xl p-0 overflow-hidden flex flex-col" style="background-color: {{ $card['bg_color'] }}; color: {{ $card['text_color'] }};">
                        @if(!empty($card['image']))
                            <div class="h-40 overflow-hidden">
                                <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <div class="p-5 flex flex-col gap-3">
                            <h3 class="text-2xl font-semibold">{{ $card['title'] }}</h3>
                            @if($card['type'] === 'vip')
                                <button data-open-notify
                                        class="w-full rounded-full py-3 font-semibold transition vip-button"
                                        style="--vip-button-bg: {{ $card['bg_color'] }}; --vip-button-text: {{ $card['text_color'] }}; font-size: {{ $settings->button_font_size_cover ?? 18 }}px;">
                                    {{ $card['title'] }}
                                </button>
                            @else
                                <button onclick="window.location.href='{{ $card['action'] }}'"
                                        class="w-full rounded-full py-3 font-semibold transition"
                                        style="background-color: var(--accent-color); font-size: {{ $settings->button_font_size_cover ?? 18 }}px;">
                                    Abrir sección
                                </button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>
        </div>
    </main>

    <!-- Redes sociales abajo -->
    <footer class="fixed bottom-6 left-0 right-0 z-40">
        <div class="flex justify-center gap-6">
            <a href="{{ $settings->facebook_url ?? '#' }}" target="_blank" 
               class="w-12 h-12 bg-[{{ $settings->button_color_cover ?? '#000' }}] flex items-center justify-center rounded-full transition hover:scale-110 hover:bg-white hover:text-black"
               style="color: var(--cover-text-strong);">
                <i class="fab fa-facebook-f"></i>
            </a>
            
            <a href="{{ $settings->instagram_url ?? '#' }}" target="_blank" 
               class="w-12 h-12 bg-[{{ $settings->button_color_cover ?? '#000' }}] flex items-center justify-center rounded-full transition hover:scale-110 hover:bg-white hover:text-black"
               style="color: var(--cover-text-strong);">
                <i class="fab fa-instagram"></i>
            </a>
            <a href="tel:{{ $settings->phone_number ?? '#' }}" 
               class="w-12 h-12 bg-[{{ $settings->button_color_cover ?? '#000' }}] flex items-center justify-center rounded-full transition hover:scale-110 hover:bg-white hover:text-black"
               style="color: var(--cover-text-strong);">
                <i class="fas fa-phone"></i>
            </a>
        </div>
    </footer>

    <!-- Modal de notificación -->
    <div id="notifyModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center px-4 {{ ($errors->has('name') || $errors->has('email')) ? '' : 'hidden' }} z-50">
        <div class="bg-white text-slate-900 rounded-3xl w-full max-w-md p-6 relative">
            <button id="closeNotifyModal" class="absolute top-4 right-4 text-2xl text-slate-500 hover:text-slate-800">&times;</button>
            <p class="text-xs uppercase tracking-[0.35em] text-amber-500 mb-2">Experiencias</p>
            <h2 class="text-2xl font-semibold mb-2">Recibe las alertas VIP</h2>
            <p class="text-sm text-slate-500 mb-4">Entérate primero de nuevas experiencias, cenas especiales y eventos privados.</p>
            <form action="{{ route('experiences.notify.cover') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <input type="text" name="name" placeholder="Tu nombre" value="{{ old('name') }}"
                           class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <input type="email" name="email" placeholder="Correo electrónico" value="{{ old('email') }}"
                           class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white py-3 rounded-2xl font-semibold hover:bg-slate-800 transition">
                    Quiero recibir noticias
                </button>
                <p class="text-xs text-slate-400 text-center">Prometemos solo enviar experiencias relevantes.</p>
            </form>
        </div>
    </div>

    @php
        $featuredGroupsPayload = $featuredGroups->mapWithKeys(function ($group) {
            $items = collect($group['items'] ?? [])->map(function ($item) {
                return [
                    'title' => $item['title'] ?? '',
                    'subtitle' => $item['subtitle'] ?? '',
                    'price' => isset($item['price']) ? number_format($item['price'], 2) : null,
                    'image' => $item['image'] ?? null,
                    'link' => $item['link'] ?? '#',
                ];
            });

            return [
                $group['slug'] => [
                    'title' => $group['title'] ?? '',
                    'subtitle' => $group['subtitle'] ?? '',
                    'source' => $group['source_label'] ?? '',
                    'items' => $items,
                ],
            ];
        });
    @endphp

    <div id="coverPopupModal" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center px-4">
        <div class="bg-white text-slate-900 rounded-3xl w-full max-w-2xl p-4 relative">
            <button type="button" class="absolute top-3 right-3 text-2xl text-slate-500 hover:text-slate-900" onclick="closeCoverPopup()">&times;</button>
            <div class="space-y-3">
                <h3 id="coverPopupTitle" class="text-xl font-semibold text-center"></h3>
                <img id="coverPopupImage" src="" alt="Anuncio" class="w-full rounded-2xl object-cover">
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/flowbite@2.3.0/dist/flowbite.min.js"></script>
    <script>
        let coverPopupInstance;
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('notifyModal');
            const openButtons = document.querySelectorAll('[data-open-notify]');
            const closeBtn = document.getElementById('closeNotifyModal');
            const flash = document.getElementById('subscriptionStatus');
            const isRegistered = localStorage.getItem('eventNotifyRegistered') === '1';

            const openModal = () => {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };
            const closeModal = () => {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            openButtons.forEach(btn => btn.addEventListener('click', openModal));
            closeBtn?.addEventListener('click', closeModal);
            modal?.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });

            if (flash) {
                localStorage.setItem('eventNotifyRegistered', '1');
            }

            if (isRegistered) {
                openButtons.forEach(btn => btn.textContent = 'Actualizar datos');
            } else if (window.innerWidth < 768 && !modal.classList.contains('hidden')) {
                // already open due to errors
            } else if (window.innerWidth < 768 && !isRegistered) {
                setTimeout(() => {
                    if (modal.classList.contains('hidden')) {
                        openModal();
                    }
                }, 2000);
            }

            const featuredData = @json($featuredGroupsPayload);
            const featuredTextColor = "{{ $featuredCardText }}";
            const featuredMutedColor = "{{ $featuredMutedText }}";
            const featuredBorderColor = "{{ $featuredBorderColor }}";
            const featuredAccentColor = "{{ $settings->button_color_cover ?? $featuredCardText }}";

            const featuredButtons = document.querySelectorAll('[data-featured-tab]');
            const headingTitleEl = document.getElementById('featuredHeadingTitle');
            const headingSubtitleEl = document.getElementById('featuredHeadingSubtitle');
            const tagEl = document.getElementById('featuredTag');
            const titleEl = document.getElementById('featuredTitle');
            const descriptionEl = document.getElementById('featuredDescription');
            const itemsEl = document.getElementById('featuredItems');
            const coverPopups = @json($popups ?? []);
            const now = new Date();
            const today = now.getDay();

            const getItemsArray = (value) => {
                if (Array.isArray(value)) {
                    return value;
                }
                if (value && typeof value === 'object') {
                    return Object.values(value);
                }
                return [];
            };

            const renderFeatured = (slug) => {
                const group = featuredData[slug];
                if (!group || !tagEl || !titleEl || !descriptionEl || !itemsEl) {
                    return;
                }

                const items = getItemsArray(group.items);

                if (headingTitleEl) {
                    headingTitleEl.textContent = group.title || '';
                }
                if (headingSubtitleEl) {
                    headingSubtitleEl.textContent = group.subtitle || '';
                }
                tagEl.textContent = group.subtitle || '';
                titleEl.textContent = group.title || '';
                descriptionEl.textContent = group.source || '';
                itemsEl.innerHTML = items.length
                    ? items.map(item => `
                        <a href="${item.link || '#'}" class="flex items-start justify-between gap-4 pb-3 group" style="color:${featuredTextColor}; border-bottom:1px solid ${featuredBorderColor};">
                            <div class="flex items-start gap-3">
                                ${item.image
                                    ? `<img src="${item.image}" alt="${item.title || ''}" class="w-14 h-14 rounded-xl object-cover border" style="border-color:${featuredBorderColor};">`
                                    : `<div class="w-14 h-14 rounded-xl flex items-center justify-center text-lg" style="border:1px solid ${featuredBorderColor};">☆</div>`
                                }
                                <div>
                                    <p class="text-lg font-semibold">${item.title ?? ''}</p>
                                    <p class="text-sm line-clamp-2" style="color:${featuredMutedColor};">${item.subtitle ?? ''}</p>
                                </div>
                            </div>
                            ${item.price ? `<span class="font-semibold" style="color:${featuredAccentColor};">$${item.price}</span>` : ''}
                        </a>
                    `).join('')
                    : '<p class="cover-text-soft text-sm">Agrega elementos destacados desde el panel para mostrarlos aquí.</p>';

                featuredButtons.forEach(btn => {
                    btn.style.backgroundColor = btn.dataset.featuredTab === slug ? btn.dataset.activeBg : btn.dataset.inactiveBg;
                    btn.style.borderColor = featuredBorderColor;
                    btn.style.color = featuredTextColor;
                });
            };

            featuredButtons.forEach(btn => {
                btn.addEventListener('click', () => renderFeatured(btn.dataset.featuredTab));
            });

            if (featuredButtons.length) {
                renderFeatured(featuredButtons[0].dataset.featuredTab);
            }

            coverPopups.forEach(popup => {
                const start = popup.start_date ? new Date(popup.start_date) : null;
                const end = popup.end_date ? new Date(popup.end_date) : null;
                const repeatDays = popup.repeat_days ? popup.repeat_days.split(',').map(day => parseInt(day, 10)) : [];
                const withinDates = (!start || now >= start) && (!end || now <= end);
                const matchesDay = repeatDays.length === 0 || repeatDays.includes(today);

                if (popup.active && popup.view === 'cover' && withinDates && matchesDay) {
                    showCoverPopup(popup);
                }
            });

            // If validation errors opened the modal, highlight status
            if (!modal.classList.contains('hidden')) {
                document.body.classList.add('overflow-hidden');
            }
        });

        function showCoverPopup(popup) {
            const modalEl = document.getElementById('coverPopupModal');
            if (!modalEl) {
                return;
            }
            if (!coverPopupInstance) {
                coverPopupInstance = new Modal(modalEl, { closable: true });
            }
            const basePath = '{{ asset('storage') }}/';
            document.getElementById('coverPopupTitle').textContent = popup.title || '';
            document.getElementById('coverPopupImage').src = popup.image ? basePath + popup.image : '';
            coverPopupInstance.show();
        }

        function closeCoverPopup() {
            if (coverPopupInstance) {
                coverPopupInstance.hide();
            }
        }
    </script>

</body>
</html>
