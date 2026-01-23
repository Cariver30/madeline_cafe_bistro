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

        $accentColor = $settings->button_color_specials ?? $settings->button_color_menu ?? '#FFB347';
        $textColor = $settings->text_color_specials ?? $settings->text_color_menu ?? '#ffffff';
        $backgroundImage = $settings->background_image_specials ?? $settings->background_image_menu ?? null;
        $categoryBg = $settings->category_name_bg_color_specials ?? $settings->category_name_bg_color_menu ?? '#111827';
        $categoryText = $settings->category_name_text_color_specials ?? $settings->category_name_text_color_menu ?? '#ffffff';
        $categoryFontSize = $settings->category_name_font_size_specials ?? $settings->category_name_font_size_menu ?? 18;
        $cardOpacity = $settings->card_opacity_specials ?? $settings->card_opacity_menu ?? 0.85;
        $cardHex = $settings->card_bg_color_specials ?? $settings->card_bg_color_menu ?? '#111827';
        $fontFamily = $settings->font_family_specials ?? $settings->font_family_menu ?? 'ui-sans-serif';

        if (!function_exists('specials_rgba')) {
            function specials_rgba(?string $hex, $opacity) {
                $opacity = is_numeric($opacity) ? max(0, min(1, (float) $opacity)) : 0.85;
                if (!$hex) {
                    return "rgba(17,24,39,{$opacity})";
                }
                $hex = ltrim($hex, '#');
                if (strlen($hex) === 3) {
                    $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                }
                if (strlen($hex) !== 6) {
                    return "rgba(17,24,39,{$opacity})";
                }
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                return "rgba({$r},{$g},{$b},{$opacity})";
            }
        }
        $cardBg = specials_rgba($cardHex, $cardOpacity);
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
            --specials-card-bg: {{ $cardBg }};
        }

        body {
            font-family: {{ $fontFamily }};
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
            background-color: var(--specials-card-bg);
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
                                        <h3 class="font-semibold"
                                            style="background-color: {{ $categoryBg }}; color: {{ $categoryText }}; font-size: {{ $categoryFontSize }}px; border-radius: 12px; padding: 6px 14px;">
                                            {{ $categoryData['category']->name }}
                                        </h3>
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
                                                    <div class="flex items-start justify-between gap-2">
                                                        <h4 class="text-lg font-semibold">{{ $item->name }}</h4>
                                                        @if(!is_null($itemData['price_special']))
                                                            <div class="text-right">
                                                                @if($itemData['show_regular_price'] && !is_null($itemData['price_regular']))
                                                                    <div class="text-xs text-white/50 line-through">
                                                                        ${{ number_format($itemData['price_regular'], 2) }}
                                                                    </div>
                                                                @endif
                                                                <div class="text-sm font-semibold" style="color: var(--specials-accent);">
                                                                    ${{ number_format($itemData['price_special'], 2) }}
                                                                </div>
                                                            </div>
                                                        @elseif(!is_null($itemData['price_regular']))
                                                            <span class="text-sm font-semibold" style="color: var(--specials-accent);">
                                                                ${{ number_format($itemData['price_regular'], 2) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if(!empty($item->description))
                                                        <p class="text-sm text-white/75 line-clamp-3">{{ $item->description }}</p>
                                                    @else
                                                        <p class="text-sm text-white/50">Consulta por disponibilidad en barra.</p>
                                                    @endif
                                                    @if(!empty($itemData['offer_label']))
                                                        <p class="text-xs text-amber-200 uppercase tracking-[0.25em]">
                                                            {{ $itemData['offer_label'] }}
                                                        </p>
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

    <div id="specialsPopupModal" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true"
         class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-black/70">
        <div class="relative w-full max-w-3xl">
            <div class="bg-white rounded-3xl shadow-lg text-slate-900 p-4 relative">
                <button onclick="closeSpecialsPopup()" class="absolute top-4 right-4 text-2xl text-slate-500 hover:text-slate-900">&times;</button>
                <div class="space-y-3">
                    <h3 id="specialsPopupTitle" class="text-xl font-semibold text-center"></h3>
                    <img id="specialsPopupImage" class="w-full rounded-2xl object-cover" alt="Promoción especial">
                </div>
            </div>
        </div>
    </div>

    @include('components.floating-nav', [
        'settings' => $settings,
        'background' => $settings->floating_bar_bg_menu ?? 'rgba(0,0,0,0.55)',
        'buttonColor' => $accentColor,
    ])

    <script src="https://unpkg.com/flowbite@2.3.0/dist/flowbite.min.js"></script>
    <script>
        let specialsPopupInstance;

        document.addEventListener('DOMContentLoaded', () => {
            const popups = @json($popups ?? []);
            const now = new Date();
            const today = now.getDay();

            popups.forEach(popup => {
                const start = popup.start_date ? new Date(popup.start_date) : null;
                const end = popup.end_date ? new Date(popup.end_date) : null;
                const repeatDays = popup.repeat_days ? popup.repeat_days.split(',').map(day => parseInt(day, 10)) : [];
                const withinDates = (!start || now >= start) && (!end || now <= end);
                const matchesDay = repeatDays.length === 0 || repeatDays.includes(today);

                if (popup.active && popup.view === 'specials' && withinDates && matchesDay) {
                    showSpecialsPopup(popup);
                }
            });
        });

        function showSpecialsPopup(popup) {
            const modalEl = document.getElementById('specialsPopupModal');
            if (!specialsPopupInstance) {
                specialsPopupInstance = new Modal(modalEl, { closable: true });
            }
            const imageBase = '{{ asset('storage') }}/';
            document.getElementById('specialsPopupImage').src = popup.image ? imageBase + popup.image : '';
            document.getElementById('specialsPopupTitle').textContent = popup.title || 'Especial del día';
            specialsPopupInstance.show();
        }

        function closeSpecialsPopup() {
            if (specialsPopupInstance) {
                specialsPopupInstance.hide();
            }
        }
    </script>
</body>
</html>
