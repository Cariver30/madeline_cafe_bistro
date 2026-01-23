<?php

namespace App\Http\Controllers;

use App\Models\CantinaCategory;
use App\Models\CantinaItem;
use App\Models\Category;
use App\Models\Cocktail;
use App\Models\CocktailCategory;
use App\Models\Setting;
use App\Models\Special;
use App\Models\Wine;
use App\Models\WineCategory;
use App\Models\Popup;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SpecialPublicController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        $now = now();

        $specials = Special::with(['categories', 'items'])
            ->where('active', true)
            ->latest()
            ->get();

        $popups = Popup::where('active', 1)
            ->where('view', 'specials')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();

        $specialCards = [];

        foreach ($specials as $special) {
            if (!$this->isWithinSchedule($now, $special->days_of_week, $special->starts_at, $special->ends_at)) {
                continue;
            }

            $scopes = $this->buildScopes($special, $settings, $now);

            if (empty($scopes)) {
                continue;
            }

            $specialCards[] = [
                'special' => $special,
                'schedule_label' => $this->formatSchedule($special->days_of_week, $special->starts_at, $special->ends_at),
                'scopes' => $scopes,
            ];
        }

        return view('specials.index', [
            'settings' => $settings,
            'specialCards' => $specialCards,
            'popups' => $popups,
        ]);
    }

    protected function buildScopes(Special $special, ?Setting $settings, Carbon $now): array
    {
        $scopeConfig = [
            'menu' => [
                'label' => $settings?->tab_label_menu ?? $settings?->button_label_menu ?? 'Menú',
                'enabled' => $settings?->show_tab_menu ?? true,
                'categoryModel' => Category::class,
                'itemModel' => \App\Models\Dish::class,
            ],
            'cocktails' => [
                'label' => $settings?->tab_label_cocktails ?? $settings?->button_label_cocktails ?? 'Cócteles',
                'enabled' => $settings?->show_tab_cocktails ?? true,
                'categoryModel' => CocktailCategory::class,
                'itemModel' => Cocktail::class,
            ],
            'wines' => [
                'label' => $settings?->tab_label_wines ?? $settings?->button_label_wines ?? 'Café & Brunch',
                'enabled' => $settings?->show_tab_wines ?? true,
                'categoryModel' => WineCategory::class,
                'itemModel' => Wine::class,
            ],
            'cantina' => [
                'label' => $settings?->tab_label_cantina ?? $settings?->button_label_cantina ?? 'Cantina',
                'enabled' => $settings?->show_tab_cantina ?? true,
                'categoryModel' => CantinaCategory::class,
                'itemModel' => CantinaItem::class,
            ],
        ];

        $categoryOverrides = $special->categories
            ->where('active', true)
            ->groupBy('scope')
            ->map(fn (Collection $items) => $items->keyBy('category_id'));

        $itemOverrides = $special->items
            ->where('active', true)
            ->groupBy('scope');

        $result = [];

        foreach ($scopeConfig as $scopeKey => $config) {
            if (isset($config['enabled']) && ! $config['enabled']) {
                continue;
            }
            $itemsForScope = $itemOverrides->get($scopeKey, collect());
            if ($itemsForScope->isEmpty()) {
                continue;
            }

            $itemIds = $itemsForScope->pluck('item_id')->unique()->values()->all();
            $itemModels = $config['itemModel']::query()
                ->whereIn('id', $itemIds)
                ->where('visible', true)
                ->with(['category:id,name,order'])
                ->orderBy('position')
                ->orderBy('id')
                ->get()
                ->keyBy('id');

            if ($itemModels->isEmpty()) {
                continue;
            }

            $itemsByCategory = [];

            foreach ($itemsForScope as $itemOverride) {
                $item = $itemModels->get($itemOverride->item_id);
                if (!$item) {
                    continue;
                }

                $categoryId = $itemOverride->category_id ?? $item->category_id;
                $categoryOverride = $categoryOverrides->get($scopeKey)?->get($categoryId);

                $categoryDays = $categoryOverride?->days_of_week ?? $special->days_of_week;
                $categoryStartsAt = $categoryOverride?->starts_at ?? $special->starts_at;
                $categoryEndsAt = $categoryOverride?->ends_at ?? $special->ends_at;

                $days = $itemOverride->days_of_week ?? $categoryDays;
                $startsAt = $itemOverride->starts_at ?? $categoryStartsAt;
                $endsAt = $itemOverride->ends_at ?? $categoryEndsAt;

                if (!$this->isWithinSchedule($now, $days, $startsAt, $endsAt)) {
                    continue;
                }

                $itemHasOverride = $this->hasOverride($itemOverride);
                $offerLabel = $this->resolveOfferLabel($itemOverride);
                $itemsByCategory[$categoryId][] = [
                    'item' => $item,
                    'override' => $itemOverride,
                    'schedule_label' => $itemHasOverride ? $this->formatSchedule($days, $startsAt, $endsAt) : null,
                    'show_schedule' => $itemHasOverride,
                    'offer_label' => $offerLabel,
                ];
            }

            if (empty($itemsByCategory)) {
                continue;
            }

            foreach ($itemsByCategory as &$items) {
                usort($items, function ($a, $b) {
                    $posA = $a['item']->position ?? 0;
                    $posB = $b['item']->position ?? 0;
                    return $posA <=> $posB ?: $a['item']->id <=> $b['item']->id;
                });
            }

            $categoryIds = array_keys($itemsByCategory);
            $categories = $config['categoryModel']::query()
                ->whereIn('id', $categoryIds)
                ->orderBy('order')
                ->orderBy('id')
                ->get()
                ->keyBy('id');

            $orderedCategories = [];
            foreach ($categories as $categoryId => $category) {
                if (!empty($itemsByCategory[$categoryId])) {
                    $categoryOverride = $categoryOverrides->get($scopeKey)?->get($categoryId);
                    $categorySchedule = null;
                    if ($categoryOverride && $this->hasOverride($categoryOverride)) {
                        $categorySchedule = $this->formatSchedule(
                            $categoryOverride->days_of_week ?? $special->days_of_week,
                            $categoryOverride->starts_at ?? $special->starts_at,
                            $categoryOverride->ends_at ?? $special->ends_at
                        );
                    }

                    $orderedCategories[] = [
                        'category' => $category,
                        'items' => $itemsByCategory[$categoryId],
                        'schedule_label' => $categorySchedule,
                    ];
                }
            }

            if (!empty($orderedCategories)) {
                $result[$scopeKey] = [
                    'label' => $config['label'],
                    'categories' => $orderedCategories,
                ];
            }
        }

        return $result;
    }

    protected function isWithinSchedule(Carbon $now, ?array $days, $startsAt, $endsAt): bool
    {
        if (is_array($days)) {
            $days = array_values(array_unique(array_map('intval', $days)));
        }

        $start = $startsAt ? $now->copy()->setTimeFromTimeString($startsAt) : null;
        $end = $endsAt ? $now->copy()->setTimeFromTimeString($endsAt) : null;

        if (!empty($days)) {
            $dayToCheck = $now->dayOfWeek;

            if ($start && $end && $end->lt($start)) {
                $nowTime = $now->format('H:i:s');
                if ($endsAt && $nowTime <= $endsAt) {
                    $dayToCheck = ($dayToCheck + 6) % 7;
                }
            }

            if (!in_array($dayToCheck, $days, true)) {
                return false;
            }
        }

        if (!$start && !$end) {
            return true;
        }

        if ($start && !$end) {
            return $now->gte($start);
        }

        if (!$start && $end) {
            return $now->lte($end);
        }

        if ($end->gte($start)) {
            return $now->between($start, $end);
        }

        return $now->gte($start) || $now->lte($end);
    }

    protected function formatSchedule(?array $days, $startsAt, $endsAt): string
    {
        $dayLabels = [
            0 => 'Dom',
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mié',
            4 => 'Jue',
            5 => 'Vie',
            6 => 'Sáb',
        ];

        $daysLabel = 'Todos los días';
        if (!empty($days)) {
            $labels = array_map(fn ($day) => $dayLabels[$day] ?? $day, $days);
            $daysLabel = implode(', ', $labels);
        }

        $timeLabel = 'Todo el día';
        if ($startsAt || $endsAt) {
            $startLabel = $this->formatTime($startsAt) ?? '—';
            $endLabel = $this->formatTime($endsAt) ?? '—';
            $timeLabel = trim($startLabel . ' - ' . $endLabel);
        }

        return $daysLabel . ' · ' . $timeLabel;
    }

    protected function formatTime($value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim((string) $value);

        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value) === 1) {
            $normalized = substr($value, 0, 5);
            try {
                return Carbon::createFromFormat('H:i', $normalized)->format('g:ia');
            } catch (\Throwable $e) {
                return $normalized;
            }
        }

        try {
            return Carbon::parse($value)->format('g:ia');
        } catch (\Throwable $e) {
            return $value;
        }
    }

    protected function hasOverride($model): bool
    {
        if (!$model) {
            return false;
        }

        $days = $model->days_of_week ?? null;
        if (is_array($days) && !empty($days)) {
            return true;
        }

        $startsAt = $model->starts_at ?? null;
        $endsAt = $model->ends_at ?? null;

        if (!is_null($startsAt) && trim((string) $startsAt) !== '') {
            return true;
        }

        if (!is_null($endsAt) && trim((string) $endsAt) !== '') {
            return true;
        }

        return false;
    }

    protected function resolveOfferLabel($override): ?string
    {
        if (!$override) {
            return null;
        }

        $text = trim((string) ($override->offer_text ?? ''));
        if ($text !== '') {
            return $text;
        }

        $type = $override->offer_type ?? null;
        $value = $override->offer_value ?? null;

        if (!$type) {
            return null;
        }

        return match ($type) {
            'percent' => $value ? ((float) $value) . '% de descuento' : 'Descuento especial',
            'fixed_price' => $value ? 'Precio especial $' . number_format((float) $value, 2) : 'Precio especial',
            'two_for_one' => '2x1 en este item',
            'custom' => 'Oferta especial',
            default => null,
        };
    }
}
