<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @php
        $cantinaLabel = trim($settings->tab_label_cantina ?? $settings->button_label_cantina ?? 'Cantina');
        $seoTitle = 'Kfeina · ' . $cantinaLabel . ' · Barra de la casa';
        $seoDescription = 'Descubre la selección de la cantina: especiales, cervezas y coctelería.';
        $seoImage = $settings?->logo
            ? asset('storage/' . $settings->logo)
            : asset('storage/default-logo.png');
        $textColor = $settings->text_color_cantina ?? $settings->text_color_menu ?? '#ffffff';
        $buttonColor = $settings->button_color_cantina ?? $settings->button_color_menu ?? '#FFB347';
        $cardBg = $settings->card_bg_color_cantina ?? $settings->card_bg_color_menu ?? '#191919';
        $cardOpacity = $settings->card_opacity_cantina ?? $settings->card_opacity_menu ?? 0.9;
        $categoryBg = $settings->category_name_bg_color_cantina ?? $settings->category_name_bg_color_menu ?? 'rgba(254, 90, 90, 0.8)';
        $categoryText = $settings->category_name_text_color_cantina ?? $settings->category_name_text_color_menu ?? '#f9f9f9';
        $categoryFontSize = $settings->category_name_font_size_cantina ?? $settings->category_name_font_size_menu ?? 30;
    @endphp
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
            --cantina-text-color: {{ $textColor }};
            --cantina-accent-color: {{ $buttonColor }};
        }
        html, body {
            min-height: 100vh;
        }

        body {
            font-family: {{ $settings->font_family_cantina ?? $settings->font_family_menu ?? 'ui-sans-serif' }};
            @if($settings && $settings->background_image_cantina)
                background: none;
            @elseif($settings && $settings->background_image_menu)
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
            @if($settings && $settings->background_image_cantina)
                background: url('{{ asset('storage/' . $settings->background_image_cantina) }}') no-repeat center center;
                background-size: cover;
            @elseif($settings && $settings->background_image_menu)
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
            color: var(--cantina-accent-color);
            transform: scale(1.05);
            font-weight: 600;
        }

        .cantina-card {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
            color: var(--cantina-text-color);
        }

        .cantina-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            body {
                background-position: center top;
                background-attachment: fixed;
            }
        }
    </style>
</head>
<body class="text-white bg-black/70">

<div class="text-center py-6 relative content-layer">
    <img src="{{ asset('storage/' . ($settings->logo ?? 'default-logo.png')) }}" class="mx-auto h-28" alt="Logo">

    <button id="toggleMenu"
        class="fixed left-4 top-4 z-50 w-12 h-12 rounded-full flex items-center justify-center text-xl shadow-lg text-white lg:hidden"
        style="background-color: {{ $buttonColor }};">
        🍸
    </button>

    <button id="toggleDesktopMenu"
        class="hidden lg:flex fixed left-6 top-6 z-40 w-12 h-12 rounded-full items-center justify-center text-lg shadow-lg text-white transition hover:scale-105"
        style="background-color: {{ $buttonColor }};">
        ☰
    </button>

    <div id="desktopSidebar" class="hidden lg:block">
        <div id="desktopSidebarPanel"
             class="fixed top-0 left-0 h-full w-64 bg-white text-black p-6 space-y-2 shadow-lg overflow-y-auto transition-transform duration-300 ease-in-out lg:translate-x-0">
            @foreach ($cantinaCategories as $category)
                <a href="#category{{ $category->id }}" class="block text-lg font-semibold hover:text-blue-500 category-nav-link" data-category-target="category{{ $category->id }}">{{ $category->name }}</a>
            @endforeach
        </div>
    </div>
</div>

<div id="categoryMenu"
    class="lg:hidden fixed inset-0 bg-white text-slate-900 px-6 py-8 space-y-6 overflow-y-auto transform -translate-y-full transition-transform duration-300 z-[60]">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold tracking-[0.25em] uppercase text-slate-500">Categorías</h2>
        <button id="closeMenu" class="text-2xl text-slate-500 hover:text-slate-900">&times;</button>
    </div>
    <div class="grid grid-cols-2 gap-4">
        @foreach ($cantinaCategories as $category)
            <button class="rounded-2xl border border-slate-200 py-4 px-3 text-sm font-semibold text-left shadow bg-white hover:bg-slate-50 category-nav-link"
                    data-category-target="category{{ $category->id }}">
                {{ $category->name }}
            </button>
        @endforeach
    </div>
</div>
<div id="menuOverlay" class="fixed inset-0 bg-black/60 z-50 hidden lg:hidden"></div>

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
            @foreach ($cantinaCategories as $category)
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

<div class="max-w-5xl mx-auto px-4 pb-32 content-layer">
    @foreach ($cantinaCategories as $category)
        <section id="category{{ $category->id }}" class="mb-10 category-section" data-category-id="category{{ $category->id }}">
            <h2 class="text-3xl font-bold text-center mb-6"
                style="background-color: {{ $categoryBg }};
                       color: {{ $categoryText }};
                       font-size: {{ $categoryFontSize }}px;
                       border-radius: 10px; padding: 10px;">
                {{ $category->name }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($category->items->where('visible', true) as $item)
                    <div id="cantina{{ $item->id }}" onclick="openCantinaModal(this)"
                        class="cantina-card rounded-lg p-4 shadow-lg relative flex items-center cursor-pointer hover:scale-105 transition"
                        style="background-color: {{ $cardBg }}; opacity: {{ $cardOpacity }};"
                        data-name="{{ $item->name }}"
                        data-description="{{ $item->description ?? '' }}"
                        data-price="${{ number_format($item->price, 2) }}"
                        data-image="{{ $item->image ? asset('storage/' . $item->image) : $seoImage }}">

                        <span class="absolute top-2 right-2 text-xs bg-gray-700 text-white px-2 py-1 rounded">Ver más</span>

                        <img src="{{ $item->image ? asset('storage/' . $item->image) : $seoImage }}"
                             alt="{{ $item->name }}"
                             class="h-24 w-24 rounded-full object-cover mr-4 border border-white/10">

                        <div class="flex-1">
                            <h3 class="text-xl font-bold">{{ $item->name }}</h3>
                            <p class="text-sm mb-2">${{ number_format($item->price, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
</div>

@include('components.floating-nav', [
    'settings' => $settings,
    'background' => $settings->floating_bar_bg_cocktails ?? $settings->floating_bar_bg_menu ?? 'rgba(0,0,0,0.55)',
    'buttonColor' => $buttonColor
])

<div id="cantinaDetailsModal" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-black/70">
    <div class="relative w-full max-w-xl max-h-[90vh]">
        <div class="bg-white rounded-lg shadow-lg text-gray-900 p-6 relative overflow-y-auto max-h-[90vh]">
            <button onclick="closeCantinaModal()" class="absolute top-3 right-3 text-gray-500 hover:text-red-600 text-xl font-bold">
                ✕
            </button>
            <img id="cantinaModalImage" class="w-full h-60 object-cover rounded-lg mb-4" alt="Imagen">
            <h3 id="cantinaModalTitle" class="text-2xl font-bold mb-2"></h3>
            <p id="cantinaModalDescription" class="mb-2"></p>
            <p id="cantinaModalPrice" class="font-semibold text-lg mb-4"></p>
        </div>
    </div>
</div>

<div id="cantinaPopupModal" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-black/70">
    <div class="relative w-full max-w-3xl">
        <div class="bg-white rounded-3xl shadow-lg text-slate-900 p-4 relative">
            <button onclick="closeCantinaPopup()" class="absolute top-4 right-4 text-2xl text-slate-500 hover:text-slate-900">&times;</button>
            <div class="space-y-3">
                <h3 id="cantinaPopupTitle" class="text-xl font-semibold text-center"></h3>
                <img id="cantinaPopupImage" class="w-full rounded-2xl object-cover" alt="Promoción especial">
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/flowbite@2.3.0/dist/flowbite.min.js"></script>
<script>
    let cantinaPopupInstance;

    document.addEventListener('DOMContentLoaded', function () {
        const toggleMenuBtn = document.getElementById('toggleMenu');
        const categoryMenu = document.getElementById('categoryMenu');
        const menuOverlay = document.getElementById('menuOverlay');
        const closeMenuBtn = document.getElementById('closeMenu');
        const navLinks = document.querySelectorAll('.category-nav-link');

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

        document.querySelectorAll('.cantina-card').forEach(card => cardObserver.observe(card));

        const popups = @json($popups ?? []);
        const now = new Date();
        const today = now.getDay();

        popups.forEach(popup => {
            const start = popup.start_date ? new Date(popup.start_date) : null;
            const end = popup.end_date ? new Date(popup.end_date) : null;
            const repeatDays = popup.repeat_days ? popup.repeat_days.split(',').map(day => parseInt(day, 10)) : [];
            const withinDates = (!start || now >= start) && (!end || now <= end);
            const matchesDay = repeatDays.length === 0 || repeatDays.includes(today);

            if (popup.active && popup.view === 'cantina' && withinDates && matchesDay) {
                showCantinaPopup(popup);
            }
        });
    });

    function openCantinaModal(card) {
        const modal = document.getElementById('cantinaDetailsModal');
        const title = card.dataset.name || '';
        const description = card.dataset.description || '';
        const price = card.dataset.price || '';
        const image = card.dataset.image || '';

        document.getElementById('cantinaModalTitle').textContent = title;
        document.getElementById('cantinaModalDescription').textContent = description;
        document.getElementById('cantinaModalPrice').textContent = price;
        const modalImage = document.getElementById('cantinaModalImage');
        modalImage.src = image;
        modalImage.alt = title;

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeCantinaModal() {
        const modal = document.getElementById('cantinaDetailsModal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function showCantinaPopup(popup) {
        const modalEl = document.getElementById('cantinaPopupModal');
        if (!cantinaPopupInstance) {
            cantinaPopupInstance = new Modal(modalEl, { closable: true });
        }
        const imageBase = '{{ asset('storage') }}/';
        document.getElementById('cantinaPopupImage').src = popup.image ? imageBase + popup.image : '';
        document.getElementById('cantinaPopupTitle').textContent = popup.title || 'Especial del día';
        cantinaPopupInstance.show();
    }

    function closeCantinaPopup() {
        if (cantinaPopupInstance) {
            cantinaPopupInstance.hide();
        }
    }
</script>
</body>
</html>
