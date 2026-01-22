@php
    $menuLabel = trim($settings->tab_label_menu ?? $settings->button_label_menu ?? 'Menú');
    $cocktailLabel = trim($settings->tab_label_cocktails ?? $settings->button_label_cocktails ?? 'Cócteles');
    $coffeeLabel = trim($settings->tab_label_wines ?? $settings->button_label_wines ?? 'Café & Brunch');
    $navLinks = [
        ['href' => url('/'), 'icon' => 'fas fa-home', 'label' => 'Inicio'],
        ['href' => url('/menu'), 'icon' => 'fas fa-utensils', 'label' => $menuLabel],
        ['href' => url('/cocktails'), 'icon' => 'fas fa-cocktail', 'label' => $cocktailLabel],
        ['href' => url('/coffee'), 'icon' => 'fas fa-mug-saucer', 'label' => $coffeeLabel],
    ];
@endphp

<div class="fixed bottom-5 left-0 right-0 z-50 content-layer px-4">
    <div class="w-full lg:flex lg:justify-center">
        <div class="relative w-full lg:max-w-4xl lg:mx-auto">
            <button type="button"
                class="lg:hidden absolute left-0 top-1/2 -translate-y-1/2 z-10 h-9 w-9 rounded-full bg-black/60 text-white shadow-lg"
                data-scroll-target="floatingNavRow"
                data-scroll-direction="left"
                aria-label="Anterior">
                <i class="fas fa-chevron-left text-sm"></i>
            </button>
            <div id="floatingNavRow"
                class="flex w-full flex-nowrap items-center gap-4 px-4 py-2 rounded-3xl backdrop-blur-lg border border-white/20 shadow-2xl overflow-x-auto scroll-smooth lg:overflow-visible lg:justify-center"
                style="background-color: {{ $background ?? 'rgba(0,0,0,0.55)' }};">
                @foreach($navLinks as $link)
                    <a href="{{ $link['href'] }}"
                       class="flex flex-shrink-0 items-center gap-2 px-3 py-2 rounded-full text-sm font-semibold text-white transition hover:scale-105 whitespace-nowrap"
                       style="background-color: {{ $buttonColor ?? '#000' }};">
                        <i class="{{ $link['icon'] }} text-lg"></i>
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </div>
            <button type="button"
                class="lg:hidden absolute right-0 top-1/2 -translate-y-1/2 z-10 h-9 w-9 rounded-full bg-black/60 text-white shadow-lg"
                data-scroll-target="floatingNavRow"
                data-scroll-direction="right"
                aria-label="Siguiente">
                <i class="fas fa-chevron-right text-sm"></i>
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-scroll-target="floatingNavRow"]').forEach(button => {
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
    });
</script>
