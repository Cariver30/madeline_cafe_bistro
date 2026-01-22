@php
    $categories = $cocktailCategories ?? collect();
    $textColor = $settings->text_color_cocktails ?? '#ffffff';
    $buttonColor = $settings->button_color_cocktails ?? '#FFB347';
    $cardBg = $settings->card_bg_color_cocktails ?? '#191919';
    $cardOpacity = $settings->card_opacity_cocktails ?? 0.9;
    $categoryBg = $settings->category_name_bg_color_cocktails ?? 'rgba(254, 90, 90, 0.8)';
    $categoryText = $settings->category_name_text_color_cocktails ?? '#f9f9f9';
    $categoryFontSize = $settings->category_name_font_size_cocktails ?? 30;
    $subcategoryBg = $settings->subcategory_name_bg_color_cocktails ?? 'rgba(0, 0, 0, 0.25)';
    $subcategoryText = $settings->subcategory_name_text_color_cocktails ?? $textColor;
    $cocktailLabel = trim($settings->tab_label_cocktails ?? 'Cócteles');
    $seoTitle = 'Kfeina · ' . $cocktailLabel . ' · Barra creativa';
    $seoDescription = 'Kfeina prepara café cosechado por ellos, desayunos, brunch y una variedad de platos creativos.';
    $seoImage = $settings?->logo
        ? asset('storage/' . $settings->logo)
        : asset('storage/default-logo.png');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}" />
    <meta property="og:title" content="{{ $seoTitle }}" />
    <meta property="og:description" content="{{ $seoDescription }}" />
    <meta property="og:type" content="website" />
    <meta property="og:image" content="{{ $seoImage }}" />
    <meta property="og:site_name" content="Kfeina" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $seoTitle }}" />
    <meta name="twitter:description" content="{{ $seoDescription }}" />
    <meta name="twitter:image" content="{{ $seoImage }}" />
    <link rel="icon" href="{{ $seoImage }}" />
    <link rel="apple-touch-icon" href="{{ $seoImage }}" />

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/flowbite@2.3.0/dist/flowbite.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <style>
        :root {
            --cocktail-text-color: {{ $textColor }};
            --cocktail-accent-color: {{ $buttonColor }};
        }
        html, body {
            min-height: 100vh;
        }

        body {
            font-family: {{ $settings->font_family_cocktails ?? 'ui-sans-serif' }};
            color: var(--cocktail-text-color);
            @if($settings && $settings->background_image_cocktails)
                background: none;
            @else
                background: radial-gradient(circle at top, #120f1d, #1f1b2e);
            @endif
            background-size: cover;
            background-attachment: fixed;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -1;
            @if($settings && $settings->background_image_cocktails)
                background: url('{{ asset('storage/' . $settings->background_image_cocktails) }}') no-repeat center center;
                background-size: cover;
            @else
                background: rgba(0, 0, 0, 0.45);
            @endif
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
            color: var(--cocktail-accent-color);
            transform: scale(1.05);
            font-weight: 600;
        }

        .drink-card {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
            color: var(--cocktail-text-color);
        }

        .drink-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .subcategory-title {
            color: var(--cocktail-text-color);
            font-weight: 700;
            font-size: 0.95rem;
            margin: 0 0 1rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            background-color: rgba(0, 0, 0, 0.25);
        }

        .hero-media {
            width: 100%;
            max-height: 420px;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            border-radius: 1.5rem;
        }

        @media (max-width: 768px) {
            body {
                background-position: center top;
                background-attachment: fixed;
            }
        }
    </style>
</head>
<body class="bg-black/70 text-white">

<div class="text-center py-6 relative content-layer">
    <img src="{{ asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}" class="mx-auto h-28" alt="Logo">

    <button id="toggleMenu"
            class="fixed left-4 top-4 z-50 w-12 h-12 rounded-full flex items-center justify-center text-xl shadow-lg text-white lg:hidden"
            style="background-color: var(--cocktail-accent-color);">
        🍸
    </button>

    <div class="hidden lg:block">
        <div class="fixed top-0 left-0 h-full w-64 bg-white text-black p-6 space-y-2 shadow-lg overflow-y-auto">
            @foreach ($categories as $category)
                <a href="#category{{ $category->id }}" class="block text-lg font-semibold hover:text-blue-500 category-nav-link" data-category-target="category{{ $category->id }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </div>
</div>

@if($settings->cocktail_hero_image)
    <div class="max-w-4xl mx-auto px-4 pb-8 content-layer">
        <img src="{{ asset('storage/' . $settings->cocktail_hero_image) }}" alt="Destacado de cócteles" class="hero-media shadow-2xl border border-white/10">
    </div>
@endif

<div id="categoryMenu"
     class="lg:hidden fixed inset-0 bg-white text-slate-900 px-6 py-8 space-y-6 overflow-y-auto transform -translate-y-full transition-transform duration-300 z-[60]">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold tracking-[0.25em] uppercase text-slate-500">Categorías</h2>
        <button id="closeMenu" class="text-2xl text-slate-500 hover:text-slate-900">&times;</button>
    </div>
    <div class="grid grid-cols-2 gap-4">
        @foreach ($categories as $category)
            <button class="rounded-2xl border border-slate-200 py-4 px-3 text-sm font-semibold text-left shadow bg-white hover:bg-slate-50 category-nav-link"
                    data-category-target="category{{ $category->id }}">
                {{ $category->name }}
            </button>
        @endforeach
    </div>
</div>
<div id="menuOverlay" class="fixed inset-0 bg-black/60 z-50 hidden lg:hidden"></div>

@if($categories->count())
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
                            data-category-target="category{{ $category->id }}">
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
@endif

<div class="max-w-5xl mx-auto px-4 pb-32 content-layer">
    @forelse ($categories as $category)
        <section id="category{{ $category->id }}" class="mb-10 category-section" data-category-id="category{{ $category->id }}">
            <h2 class="text-3xl font-bold text-center mb-6"
                style="background-color: {{ $categoryBg }};
                       color: {{ $categoryText }};
                       font-size: {{ $categoryFontSize }}px;
                       border-radius: 10px; padding: 10px;">
                {{ $category->name }}
            </h2>

            @php
                $categoryItems = $category->items ?? collect();
                $subcategories = $category->subcategories ?? collect();
                $uncategorizedItems = $categoryItems->whereNull('subcategory_id');
            @endphp
            @if($subcategories->count())
                @foreach ($subcategories as $subcategory)
                    @php
                        $subcategoryItems = ($subcategory->items ?? collect())->where('visible', true);
                    @endphp
                    @if($subcategoryItems->isNotEmpty())
                        <h3 class="subcategory-title" style="background-color: {{ $subcategoryBg }}; color: {{ $subcategoryText }};">
                            {{ $subcategory->name }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            @foreach ($subcategoryItems as $drink)
                                @php
                                    $drinkExtras = $drink->extras->where('active', true);
                                    $drinkExtrasPayload = $drinkExtras->map(function ($extra) {
                                        return [
                                            'name' => $extra->name,
                                            'price' => number_format($extra->price, 2, '.', ''),
                                            'description' => $extra->description,
                                        ];
                                    });
                                @endphp
                                <div id="drink{{ $drink->id }}" onclick="openDrinkModal(this)"
                                     class="drink-card rounded-lg p-4 shadow-lg relative flex items-center cursor-pointer hover:scale-105 transition"
                                     style="background-color: {{ $cardBg }}; opacity: {{ $cardOpacity }};"
                                     data-name="{{ $drink->name }}"
                                     data-description="{{ strip_tags($drink->description) }}"
                                     data-price="${{ number_format($drink->price, 2) }}"
                                     data-image="{{ $drink->image ? asset('storage/' . $drink->image) : asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}"
                                     data-extras='@json($drinkExtrasPayload)'>

                                    <span class="absolute top-2 right-2 text-xs bg-gray-700 text-white px-2 py-1 rounded">Ver más</span>

                                    <img src="{{ $drink->image ? asset('storage/' . $drink->image) : asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}"
                                         alt="{{ $drink->name }}"
                                         class="h-24 w-24 rounded-full object-cover mr-4 border border-white/10">

                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold">{{ $drink->name }}</h3>
                                        <p class="text-sm mb-2">${{ number_format($drink->price, 2) }}</p>


                                        @if (!empty($drink->volume) || !empty($drink->garnish))
                                            <div class="flex flex-wrap gap-2 text-xs">
                                                @if(!empty($drink->volume))
                                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full border border-white/10 bg-white/5">
                                                        <i class="fas fa-glass-whiskey text-[var(--cocktail-accent-color)]"></i> {{ $drink->volume }}
                                                    </span>
                                                @endif
                                                @if(!empty($drink->garnish))
                                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full border border-white/10 bg-white/5">
                                                        <i class="fas fa-leaf text-[var(--cocktail-accent-color)]"></i> {{ $drink->garnish }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
                @if($uncategorizedItems->count())
                    <h3 class="subcategory-title" style="background-color: {{ $subcategoryBg }}; color: {{ $subcategoryText }};">Otros</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($uncategorizedItems as $drink)
                            @php
                                $drinkExtras = $drink->extras->where('active', true);
                                $drinkExtrasPayload = $drinkExtras->map(function ($extra) {
                                    return [
                                        'name' => $extra->name,
                                        'price' => number_format($extra->price, 2, '.', ''),
                                        'description' => $extra->description,
                                    ];
                                });
                            @endphp
                            <div id="drink{{ $drink->id }}" onclick="openDrinkModal(this)"
                                 class="drink-card rounded-lg p-4 shadow-lg relative flex items-center cursor-pointer hover:scale-105 transition"
                                 style="background-color: {{ $cardBg }}; opacity: {{ $cardOpacity }};"
                                 data-name="{{ $drink->name }}"
                                 data-description="{{ strip_tags($drink->description) }}"
                                 data-price="${{ number_format($drink->price, 2) }}"
                                 data-image="{{ $drink->image ? asset('storage/' . $drink->image) : asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}"
                                 data-extras='@json($drinkExtrasPayload)'>

                                <span class="absolute top-2 right-2 text-xs bg-gray-700 text-white px-2 py-1 rounded">Ver más</span>

                                <img src="{{ $drink->image ? asset('storage/' . $drink->image) : asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}"
                                     alt="{{ $drink->name }}"
                                     class="h-24 w-24 rounded-full object-cover mr-4 border border-white/10">

                                <div class="flex-1">
                                    <h3 class="text-xl font-bold">{{ $drink->name }}</h3>
                                    <p class="text-sm mb-2">${{ number_format($drink->price, 2) }}</p>


                                    @if (!empty($drink->volume) || !empty($drink->garnish))
                                        <div class="flex flex-wrap gap-2 text-xs">
                                            @if(!empty($drink->volume))
                                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full border border-white/10 bg-white/5">
                                                    <i class="fas fa-glass-whiskey text-[var(--cocktail-accent-color)]"></i> {{ $drink->volume }}
                                                </span>
                                            @endif
                                            @if(!empty($drink->garnish))
                                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full border border-white/10 bg-white/5">
                                                    <i class="fas fa-leaf text-[var(--cocktail-accent-color)]"></i> {{ $drink->garnish }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($categoryItems->where('visible', true) as $drink)
                        @php
                            $drinkExtras = $drink->extras->where('active', true);
                            $drinkExtrasPayload = $drinkExtras->map(function ($extra) {
                                return [
                                    'name' => $extra->name,
                                    'price' => number_format($extra->price, 2, '.', ''),
                                    'description' => $extra->description,
                                ];
                            });
                        @endphp
                        <div id="drink{{ $drink->id }}" onclick="openDrinkModal(this)"
                             class="drink-card rounded-lg p-4 shadow-lg relative flex items-center cursor-pointer hover:scale-105 transition"
                             style="background-color: {{ $cardBg }}; opacity: {{ $cardOpacity }};"
                             data-name="{{ $drink->name }}"
                             data-description="{{ strip_tags($drink->description) }}"
                             data-price="${{ number_format($drink->price, 2) }}"
                             data-image="{{ $drink->image ? asset('storage/' . $drink->image) : asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}"
                             data-extras='@json($drinkExtrasPayload)'>

                            <span class="absolute top-2 right-2 text-xs bg-gray-700 text-white px-2 py-1 rounded">Ver más</span>

                            <img src="{{ $drink->image ? asset('storage/' . $drink->image) : asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}"
                                 alt="{{ $drink->name }}"
                                 class="h-24 w-24 rounded-full object-cover mr-4 border border-white/10">

                            <div class="flex-1">
                                <h3 class="text-xl font-bold">{{ $drink->name }}</h3>
                                <p class="text-sm mb-2">${{ number_format($drink->price, 2) }}</p>


                                @if (!empty($drink->volume) || !empty($drink->garnish))
                                    <div class="flex flex-wrap gap-2 text-xs">
                                        @if(!empty($drink->volume))
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full border border-white/10 bg-white/5">
                                                <i class="fas fa-glass-whiskey text-[var(--cocktail-accent-color)]"></i> {{ $drink->volume }}
                                            </span>
                                        @endif
                                        @if(!empty($drink->garnish))
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full border border-white/10 bg-white/5">
                                                <i class="fas fa-leaf text-[var(--cocktail-accent-color)]"></i> {{ $drink->garnish }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @empty
        <div class="text-center py-20 text-white/80">
            No hay cócteles configurados. Usa el panel para añadir elementos a esta vista.
        </div>
    @endforelse
</div>

@include('components.floating-nav', [
    'settings' => $settings,
    'background' => $settings->floating_bar_bg_cocktails ?? $settings->floating_bar_bg_menu ?? 'rgba(0,0,0,0.55)',
    'buttonColor' => $buttonColor
])

<div id="drinkDetailsModal" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-black/70">
    <div class="relative w-full max-w-xl max-h-[90vh]">
        <div class="bg-white rounded-lg shadow-lg text-gray-900 p-6 relative overflow-y-auto max-h-[90vh]">
            <button onclick="closeDrinkModal()" class="absolute top-3 right-3 text-gray-500 hover:text-red-600 text-xl font-bold">
                ✕
            </button>

            <img id="drinkModalImage" class="w-full h-60 object-cover rounded-lg mb-4" alt="Imagen del cóctel">

            <h3 id="drinkModalTitle" class="text-2xl font-bold mb-2"></h3>
            <p id="drinkModalDescription" class="mb-2"></p>
            <p id="drinkModalPrice" class="font-semibold text-lg mb-4"></p>

            <div id="drinkModalExtras" class="hidden mt-4">
                <h4 class="text-lg font-semibold mb-2" style="color: var(--cocktail-accent-color);">Extras sugeridos</h4>
                <ul id="drinkExtrasList" class="space-y-2 text-sm text-slate-700"></ul>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/flowbite@2.3.0/dist/flowbite.min.js"></script>
<script>
    const menuModal = document.getElementById('categoryMenu');
    const overlay = document.getElementById('menuOverlay');
    const toggleMenuBtn = document.getElementById('toggleMenu');
    const closeMenuBtn = document.getElementById('closeMenu');
    const navLinks = document.querySelectorAll('.category-nav-link');
    const scrollButtons = document.querySelectorAll('[data-scroll-target="categoryChipRow"]');

    const openMenu = () => {
        menuModal?.classList.remove('-translate-y-full');
        overlay?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeMenu = () => {
        menuModal?.classList.add('-translate-y-full');
        overlay?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    toggleMenuBtn?.addEventListener('click', () => {
        if (menuModal?.classList.contains('-translate-y-full')) {
            openMenu();
        } else {
            closeMenu();
        }
    });

    scrollButtons.forEach(button => {
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

    closeMenuBtn?.addEventListener('click', closeMenu);
    overlay?.addEventListener('click', closeMenu);

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

    const cardObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                cardObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });

    document.querySelectorAll('.drink-card').forEach(card => cardObserver.observe(card));

    function openDrinkModal(el) {
        const name = el.dataset.name;
        const description = el.dataset.description;
        const price = el.dataset.price;
        const fallbackImage = "{{ asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}";
        const image = el.dataset.image && !el.dataset.image.endsWith('/storage/') ? el.dataset.image : fallbackImage;
        const extras = el.dataset.extras ? JSON.parse(el.dataset.extras) : [];

        document.getElementById('drinkModalTitle').textContent = name;
        document.getElementById('drinkModalDescription').textContent = description;
        document.getElementById('drinkModalPrice').textContent = price;
        document.getElementById('drinkModalImage').src = image;

        const extrasSection = document.getElementById('drinkModalExtras');
        const extrasList = document.getElementById('drinkExtrasList');
        extrasList.innerHTML = '';
        if (extras.length) {
            extras.forEach(extra => {
                const li = document.createElement('li');
                li.className = 'flex flex-col gap-1 border border-slate-200 rounded-xl px-3 py-2 bg-white/70';
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between text-sm font-semibold text-slate-800';
                const nameSpan = document.createElement('span');
                nameSpan.textContent = extra.name || 'Extra';
                const priceSpan = document.createElement('span');
                const priceValue = parseFloat(extra.price ?? 0);
                priceSpan.textContent = priceValue ? `$${priceValue.toFixed(2)}` : '';
                row.appendChild(nameSpan);
                row.appendChild(priceSpan);
                li.appendChild(row);
                if (extra.description) {
                    const desc = document.createElement('p');
                    desc.className = 'text-xs text-slate-600';
                    desc.textContent = extra.description;
                    li.appendChild(desc);
                }
                extrasList.appendChild(li);
            });
            extrasSection.classList.remove('hidden');
        } else {
            extrasSection.classList.add('hidden');
        }

        if (!window.drinkModalInstance) {
            window.drinkModalInstance = new Modal(document.getElementById('drinkDetailsModal'));
        }
        window.drinkModalInstance.show();
    }

    function closeDrinkModal() {
        if (window.drinkModalInstance) {
            window.drinkModalInstance.hide();
        }
    }
</script>
</body>
</html>
