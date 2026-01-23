<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @php
        $specialsTitle = 'Kfeina · Especiales y ofertas';
        $specialsDescription = 'Especiales de la casa, happy hour y ofertas por tiempo limitado.';
        $seoImage = $settings?->logo
            ? asset('storage/' . $settings->logo)
            : asset('storage/default-logo.png');
        $accentColor = $settings->button_color_menu ?? '#FFB347';
        $textColor = $settings->text_color_menu ?? '#ffffff';
        $backgroundImage = $settings->background_image_menu ?? null;
    @endphp
    <title>{{ $specialsTitle }}</title>
    <meta name="description" content="{{ $specialsDescription }}" />
    <meta property="og:title" content="{{ $specialsTitle }}" />
    <meta property="og:description" content="{{ $specialsDescription }}" />
    <meta property="og:type" content="website" />
    <meta property="og:image" content="{{ $seoImage }}" />
    <meta property="og:site_name" content="Kfeina" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $specialsTitle }}" />
    <meta name="twitter:description" content="{{ $specialsDescription }}" />
    <meta name="twitter:image" content="{{ $seoImage }}" />
    <link rel="icon" href="{{ $seoImage }}" />
    <link rel="apple-touch-icon" href="{{ $seoImage }}" />

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <style>
        :root {
            --specials-accent: {{ $accentColor }};
            --specials-text: {{ $textColor }};
        }

        body {
            font-family: {{ $settings->font_family_menu ?? 'ui-sans-serif' }};
            color: var(--specials-text);
            background: radial-gradient(circle at top, #181818, #0f0f0f);
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            @if($backgroundImage)
                background: url('{{ asset('storage/' . $backgroundImage) }}') no-repeat center center;
                background-size: cover;
            @else
                background: linear-gradient(135deg, rgba(0, 0, 0, 0.78), rgba(0, 0, 0, 0.88));
            @endif
            z-index: -2;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at top, rgba(255, 179, 71, 0.15), transparent 60%);
            z-index: -1;
        }

        .special-card {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            background-color: rgba(15, 15, 15, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.35);
        }

        .special-card img {
            transition: transform 0.5s ease;
        }

        .special-card:hover img {
            transform: scale(1.05);
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.7rem;
            border-radius: 999px;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            background: rgba(255, 255, 255, 0.12);
        }
    </style>
</head>
<body class="text-white">
    <header class="max-w-6xl mx-auto px-4 pt-10 pb-8">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="pill text-white/70">Promociones activas</p>
                <h1 class="text-3xl md:text-4xl font-semibold mt-3">Especiales y ofertas</h1>
                <p class="text-sm text-white/70 mt-2">Platos y bebidas con precio regular, destacados por horario.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="px-4 py-2 rounded-full border border-white/30 text-sm font-semibold hover:bg-white/10">
                    Volver al inicio
                </a>
                <a href="{{ url('/menu') }}" class="px-4 py-2 rounded-full text-sm font-semibold"
                   style="background-color: var(--specials-accent); color: #1c1c1c;">
                    Ver menú
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 pb-32 space-y-12">
        @if(empty($specialCards))
            <div class="special-card p-10 text-center">
                <h2 class="text-xl font-semibold">No hay especiales disponibles ahora</h2>
                <p class="text-sm text-white/70 mt-2">Regresa más tarde para ver nuevas promociones.</p>
            </div>
        @else
            @foreach($specialCards as $specialData)
                <section class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-semibold">{{ $specialData['special']->name }}</h2>
                            <p class="text-sm text-white/70 mt-1">{{ $specialData['schedule_label'] }}</p>
                        </div>
                        <span class="pill text-white/80" style="background: rgba(255, 179, 71, 0.2); color: var(--specials-accent);">
                            Especial activo
                        </span>
                    </div>

                    @foreach($specialData['scopes'] as $scope)
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <span class="pill">{{ $scope['label'] }}</span>
                                <div class="h-px flex-1 bg-white/10"></div>
                            </div>

                            @foreach($scope['categories'] as $categoryData)
                                <div class="space-y-4">
                                    <div class="flex flex-wrap items-baseline gap-2">
                                        <h3 class="text-lg font-semibold text-white/90">{{ $categoryData['category']->name }}</h3>
                                        @if(!empty($categoryData['schedule_label']))
                                            <span class="text-[11px] uppercase tracking-[0.25em] text-white/60">
                                                {{ $categoryData['schedule_label'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                        @foreach($categoryData['items'] as $itemData)
                                            @php
                                                $item = $itemData['item'];
                                                $image = $item->image ? asset('storage/' . $item->image) : null;
                                            @endphp
                                            <article class="special-card">
                                                <div class="relative">
                                                    @if($image)
                                                        <img src="{{ $image }}" alt="{{ $item->name }}" class="h-48 w-full object-cover">
                                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                                                        <div class="absolute left-4 bottom-4">
                                                            <span class="pill" style="background: rgba(255,255,255,0.18);">Especial</span>
                                                        </div>
                                                    @else
                                                        <div class="h-48 w-full flex items-center justify-center bg-gradient-to-br from-[#1c1c1c] via-[#121212] to-[#0b0b0b]">
                                                            <div class="flex flex-col items-center gap-2 text-white/60">
                                                                <i class="fas fa-utensils text-2xl"></i>
                                                                <span class="text-xs uppercase tracking-[0.25em]">Especial</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="p-5 space-y-3">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <h4 class="text-lg font-semibold">{{ $item->name }}</h4>
                                                        @if(!is_null($item->price))
                                                            <span class="text-sm font-semibold" style="color: var(--specials-accent);">
                                                                ${{ number_format($item->price, 2) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if(!empty($item->description))
                                                        <p class="text-sm text-white/75 line-clamp-3">{{ $item->description }}</p>
                                                    @else
                                                        <p class="text-sm text-white/50">Consulta por disponibilidad en barra.</p>
                                                    @endif
                                                    @if(!empty($itemData['schedule_label']))
                                                        <p class="text-[11px] text-white/50 tracking-[0.2em] uppercase">
                                                            {{ $itemData['schedule_label'] }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </section>
            @endforeach
        @endif
    </main>

    @include('components.floating-nav', [
        'settings' => $settings,
        'background' => $settings->floating_bar_bg_menu ?? 'rgba(0,0,0,0.55)',
        'buttonColor' => $accentColor,
    ])
</body>
</html>
