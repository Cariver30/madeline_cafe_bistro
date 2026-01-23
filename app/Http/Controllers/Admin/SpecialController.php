<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CantinaCategory;
use App\Models\Category;
use App\Models\CocktailCategory;
use App\Models\Special;
use App\Models\SpecialCategory;
use App\Models\SpecialItem;
use App\Models\Setting;
use App\Models\WineCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpecialController extends Controller
{
    public function index()
    {
        $specials = Special::withCount(['categories', 'items'])
            ->latest()
            ->paginate(12);

        return view('admin.specials.index', compact('specials'));
    }

    public function create()
    {
        $settings = Setting::first();

        return view('admin.specials.create', [
            'special' => null,
            'settings' => $settings,
            'scopes' => $this->buildScopes($settings),
            'selectedCategories' => [],
            'selectedItems' => [],
            'days' => $this->daysOfWeek(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateSpecial($request);

        $special = Special::create([
            'name' => $data['name'],
            'active' => $request->boolean('active', true),
            'days_of_week' => $this->cleanDays($data['days_of_week'] ?? null),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ]);

        $this->syncRelations($special, $request);

        return redirect()->route('admin.specials.edit', $special)
            ->with('success', 'Especial creado con éxito.');
    }

    public function edit(Special $special)
    {
        $settings = Setting::first();

        $selectedCategories = $special->categories
            ->mapWithKeys(fn ($category) => [
                $this->makeKey($category->scope, $category->category_id) => $category,
            ])
            ->all();

        $selectedItems = $special->items
            ->mapWithKeys(fn ($item) => [
                $this->makeKey($item->scope, $item->item_id) => $item,
            ])
            ->all();

        return view('admin.specials.edit', [
            'special' => $special,
            'settings' => $settings,
            'scopes' => $this->buildScopes($settings),
            'selectedCategories' => $selectedCategories,
            'selectedItems' => $selectedItems,
            'days' => $this->daysOfWeek(),
        ]);
    }

    public function update(Request $request, Special $special)
    {
        $data = $this->validateSpecial($request);

        $special->update([
            'name' => $data['name'],
            'active' => $request->boolean('active', true),
            'days_of_week' => $this->cleanDays($data['days_of_week'] ?? null),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ]);

        $this->syncRelations($special, $request);

        return redirect()->route('admin.specials.edit', $special)
            ->with('success', 'Especial actualizado con éxito.');
    }

    public function destroy(Special $special)
    {
        $special->delete();

        return redirect()->route('admin.specials.index')
            ->with('success', 'Especial eliminado.');
    }

    protected function validateSpecial(Request $request): array
    {
        $request->merge([
            'starts_at' => $this->normalizeTime($request->input('starts_at')),
            'ends_at' => $this->normalizeTime($request->input('ends_at')),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'days_of_week' => ['nullable', 'array'],
            'days_of_week.*' => ['integer', 'between:0,6'],
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i'],
        ]);
    }

    protected function syncRelations(Special $special, Request $request): void
    {
        $categories = $request->input('categories', []);
        $items = $request->input('items', []);

        DB::transaction(function () use ($special, $categories, $items) {
            $special->categories()->delete();
            $special->items()->delete();

            foreach ($categories as $scope => $categoryGroup) {
                foreach ($categoryGroup as $categoryId => $payload) {
                    if (!($payload['active'] ?? false)) {
                        continue;
                    }

                    SpecialCategory::create([
                        'special_id' => $special->id,
                        'scope' => $scope,
                        'category_id' => (int) $categoryId,
                        'active' => true,
                        'days_of_week' => $this->cleanDays($payload['days_of_week'] ?? null),
                        'starts_at' => $payload['starts_at'] ?? null,
                        'ends_at' => $payload['ends_at'] ?? null,
                    ]);
                }
            }

            foreach ($items as $scope => $itemGroup) {
                foreach ($itemGroup as $itemId => $payload) {
                    if (!($payload['active'] ?? false)) {
                        continue;
                    }

                    SpecialItem::create([
                        'special_id' => $special->id,
                        'scope' => $scope,
                        'item_id' => (int) $itemId,
                        'category_id' => isset($payload['category_id']) ? (int) $payload['category_id'] : null,
                        'active' => true,
                        'days_of_week' => $this->cleanDays($payload['days_of_week'] ?? null),
                        'starts_at' => $payload['starts_at'] ?? null,
                        'ends_at' => $payload['ends_at'] ?? null,
                    ]);
                }
            }
        });
    }

    protected function buildScopes(?Setting $settings): array
    {
        $scopes = [];
        $visible = fn ($flag) => $flag ?? true;

        if ($visible($settings?->show_tab_menu)) {
            $categories = Category::with(['dishes' => function ($query) {
                    $query->where('visible', true)->orderBy('position')->orderBy('id');
                }])
                ->orderBy('order')
                ->get()
                ->filter(fn ($category) => $category->dishes->where('visible', true)->isNotEmpty())
                ->values();

            $scopes['menu'] = [
                'label' => $settings?->tab_label_menu ?? $settings?->button_label_menu ?? 'Menú',
                'categories' => $categories,
                'item_relation' => 'dishes',
            ];
        }

        if ($visible($settings?->show_tab_cocktails)) {
            $categories = CocktailCategory::with(['items' => function ($query) {
                    $query->where('visible', true)->orderBy('position')->orderBy('id');
                }])
                ->orderBy('order')
                ->get()
                ->filter(fn ($category) => $category->items->where('visible', true)->isNotEmpty())
                ->values();

            $scopes['cocktails'] = [
                'label' => $settings?->tab_label_cocktails ?? $settings?->button_label_cocktails ?? 'Cócteles',
                'categories' => $categories,
                'item_relation' => 'items',
            ];
        }

        if ($visible($settings?->show_tab_wines)) {
            $categories = WineCategory::with(['items' => function ($query) {
                    $query->where('visible', true)->orderBy('position')->orderBy('id');
                }])
                ->orderBy('order')
                ->get()
                ->filter(fn ($category) => $category->items->where('visible', true)->isNotEmpty())
                ->values();

            $scopes['wines'] = [
                'label' => $settings?->tab_label_wines ?? $settings?->button_label_wines ?? 'Café & Brunch',
                'categories' => $categories,
                'item_relation' => 'items',
            ];
        }

        if ($visible($settings?->show_tab_cantina)) {
            $categories = CantinaCategory::with(['items' => function ($query) {
                    $query->where('visible', true)->orderBy('position')->orderBy('id');
                }])
                ->orderBy('order')
                ->get()
                ->filter(fn ($category) => $category->items->where('visible', true)->isNotEmpty())
                ->values();

            $scopes['cantina'] = [
                'label' => $settings?->tab_label_cantina ?? $settings?->button_label_cantina ?? 'Cantina',
                'categories' => $categories,
                'item_relation' => 'items',
            ];
        }

        return $scopes;
    }

    protected function daysOfWeek(): array
    {
        return [
            0 => 'Dom',
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mié',
            4 => 'Jue',
            5 => 'Vie',
            6 => 'Sáb',
        ];
    }

    protected function cleanDays($value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        $days = array_values(array_unique(array_filter(array_map('intval', $value), fn ($day) => $day >= 0 && $day <= 6)));

        return empty($days) ? null : $days;
    }

    protected function makeKey(string $scope, int $id): string
    {
        return $scope . ':' . $id;
    }

    protected function normalizeTime(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim($value);
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value) !== 1) {
            return $value;
        }

        return substr($value, 0, 5);
    }
}
