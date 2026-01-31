<?php

namespace App\Support;

use App\Models\CantinaCategory;
use App\Models\CantinaItem;
use App\Models\Category;
use App\Models\CloverCategory;
use App\Models\Cocktail;
use App\Models\CocktailCategory;
use App\Models\Dish;
use App\Models\Extra;
use App\Models\Tax;
use App\Models\Wine;
use App\Models\WineCategory;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class CloverSyncService
{
    private ?array $defaultTaxIds = null;

    public function __construct(private CloverClient $client)
    {
    }

    public function syncCategories(): int
    {
        $total = 0;
        $offset = 0;
        $limit = 200;

        do {
            $payload = $this->client->listCategories($limit, $offset);
            $elements = Arr::get($payload, 'elements', []);

            foreach ($elements as $category) {
                $cloverId = $category['id'] ?? null;
                $name = $category['name'] ?? null;
                if (! $cloverId || ! $name) {
                    continue;
                }

                CloverCategory::updateOrCreate(
                    ['clover_id' => $cloverId],
                    [
                        'name' => $name,
                        'sort_order' => (int) ($category['sortOrder'] ?? 0),
                        'deleted' => (bool) ($category['deleted'] ?? false),
                    ]
                );
                $total++;
            }

            $offset += $limit;
        } while (count($elements) === $limit);

        return $total;
    }

    public function syncItems(bool $syncTaxes = true): int
    {
        $scopeMap = CloverCategory::whereNotNull('scope')
            ->pluck('scope', 'clover_id')
            ->all();

        if ($scopeMap === []) {
            return 0;
        }

        $total = 0;
        $offset = 0;
        $limit = 200;
        $validIdsByScope = [
            'menu' => [],
            'cocktails' => [],
            'wines' => [],
            'cantina' => [],
        ];

        do {
            $payload = $this->client->listItems($limit, $offset, $syncTaxes);
            $elements = Arr::get($payload, 'elements', []);

            foreach ($elements as $item) {
                $itemId = $item['id'] ?? null;
                $name = $item['name'] ?? null;
                if (! $itemId || ! $name) {
                    continue;
                }

                [$scope, $categoryId] = $this->resolveScope($item, $scopeMap);
                if (! $scope || ! $categoryId) {
                    continue;
                }

                $localCategory = $this->resolveLocalCategory($scope, $categoryId);
                if (! $localCategory) {
                    continue;
                }

                $price = $this->formatPrice($item['price'] ?? null);
                $visible = $this->resolveVisibility($item);

                $itemModel = $this->upsertItem($scope, [
                    'clover_id' => $itemId,
                    'name' => $name,
                    'description' => $this->resolveDescription($item),
                    'price' => $price,
                    'category_id' => $localCategory->id,
                    'visible' => $visible,
                ]);

                $this->syncItemModifiers($scope, $itemId, $itemModel);
                if ($syncTaxes) {
                    $this->syncItemTaxes($itemId, $item, $itemModel);
                }

                if (isset($validIdsByScope[$scope])) {
                    // Track valid Clover IDs per scope so we can hide stale items later.
                    $validIdsByScope[$scope][$itemId] = true;
                }

                $total++;
            }

            $offset += $limit;
        } while (count($elements) === $limit);

        foreach ($validIdsByScope as $scope => $idsMap) {
            if ($idsMap === []) {
                continue;
            }
            $this->deactivateMissingItems($scope, array_keys($idsMap));
        }

        return $total;
    }

    private function syncItemTaxes(string $itemId, array $item, $itemModel): void
    {
        if (!method_exists($itemModel, 'taxes')) {
            return;
        }

        $taxRates = Arr::get($item, 'taxRates.elements', []);
        $useDefault = (bool) Arr::get($item, 'defaultTaxRates', false);
        $hasExpandedTaxRates = Arr::has($item, 'taxRates');

        if (! $hasExpandedTaxRates) {
            // Avoid expensive per-item calls when we already have taxes stored.
            if ($itemModel->taxes()->exists()) {
                return;
            }

            try {
                $itemPayload = $this->client->getItemTaxRates($itemId);
            } catch (\Throwable $exception) {
                report($exception);
                return;
            }

            $taxRates = Arr::get($itemPayload, 'taxRates.elements', []);
            $useDefault = (bool) Arr::get($itemPayload, 'defaultTaxRates', $useDefault);
        }

        $taxIds = [];
        foreach ($taxRates as $taxRate) {
            $taxId = $this->upsertTaxRate($taxRate);
            if ($taxId) {
                $taxIds[] = $taxId;
            }
        }

        if ($taxIds === [] && $useDefault) {
            $taxIds = $this->getDefaultTaxIds();
        }

        $itemModel->taxes()->sync($taxIds);
    }

    private function getDefaultTaxIds(): array
    {
        if ($this->defaultTaxIds !== null) {
            return $this->defaultTaxIds;
        }

        $defaultIds = [];
        $offset = 0;
        $limit = 200;

        do {
            $payload = $this->client->listTaxRates($limit, $offset);
            $elements = Arr::get($payload, 'elements', []);

            foreach ($elements as $taxRate) {
                $taxId = $this->upsertTaxRate($taxRate);
                if (! $taxId) {
                    continue;
                }

                $isDefault = (bool) ($taxRate['isDefault'] ?? false);
                $isDeleted = (bool) ($taxRate['isDeleted'] ?? false);
                $rate = (int) ($taxRate['rate'] ?? 0);
                if ($isDefault && ! $isDeleted && $rate > 0) {
                    $defaultIds[] = $taxId;
                }
            }

            $offset += $limit;
        } while (count($elements) === $limit);

        $this->defaultTaxIds = array_values(array_unique($defaultIds));

        return $this->defaultTaxIds;
    }

    private function upsertTaxRate(array $taxRate): ?int
    {
        $cloverId = $taxRate['id'] ?? null;
        $name = $taxRate['name'] ?? null;
        if (! $cloverId || ! $name) {
            return null;
        }

        $rateRaw = (int) ($taxRate['rate'] ?? 0);
        $rate = round($rateRaw / 100000, 2);
        $active = ! (bool) ($taxRate['isDeleted'] ?? false);

        $tax = Tax::updateOrCreate(
            ['clover_id' => $cloverId],
            [
                'name' => $name,
                'rate' => $rate,
                'active' => $active,
            ]
        );

        return $tax->id;
    }

    public function topSellers(Carbon $from, Carbon $to, int $limit = 10): array
    {
        $fromMs = $from->startOfDay()->getTimestamp() * 1000;
        $toMs = $to->endOfDay()->getTimestamp() * 1000;
        $filter = [
            "createdTime>={$fromMs}",
            "createdTime<={$toMs}",
        ];

        $offset = 0;
        $pageSize = 100;
        $totals = [];

        do {
            $payload = $this->client->listOrders($pageSize, $offset, $filter, 'lineItems');
            $orders = Arr::get($payload, 'elements', []);

            foreach ($orders as $order) {
                $lineItems = Arr::get($order, 'lineItems.elements', []);
                foreach ($lineItems as $lineItem) {
                    $key = $lineItem['item']['id'] ?? $lineItem['id'] ?? $lineItem['name'] ?? uniqid('line_', true);
                    $name = $lineItem['name'] ?? ($lineItem['item']['name'] ?? 'Producto');
                    $quantity = $this->resolveQuantity($lineItem);
                    $price = (float) ($lineItem['price'] ?? 0);
                    $revenue = $price * $quantity;

                    if (! isset($totals[$key])) {
                        $totals[$key] = [
                            'name' => $name,
                            'quantity' => 0,
                            'revenue_cents' => 0,
                        ];
                    }

                    $totals[$key]['quantity'] += $quantity;
                    $totals[$key]['revenue_cents'] += $revenue;
                }
            }

            $offset += $pageSize;
        } while (count($orders) === $pageSize);

        $items = array_values($totals);
        usort($items, fn ($a, $b) => $b['quantity'] <=> $a['quantity']);

        $items = array_slice($items, 0, $limit);

        return array_map(fn ($item) => [
            'name' => $item['name'],
            'quantity' => $item['quantity'],
            'revenue' => round($item['revenue_cents'] / 100, 2),
        ], $items);
    }

    public function rangeSummary(Carbon $from, Carbon $to): array
    {
        $fromMs = $from->startOfDay()->getTimestamp() * 1000;
        $toMs = $to->endOfDay()->getTimestamp() * 1000;
        $filter = [
            "createdTime>={$fromMs}",
            "createdTime<={$toMs}",
        ];

        $offset = 0;
        $pageSize = 100;
        $salesCents = 0;
        $tipsCents = 0;
        $ordersCount = 0;

        do {
            $payload = $this->client->listOrders($pageSize, $offset, $filter, 'lineItems');
            $orders = Arr::get($payload, 'elements', []);

            foreach ($orders as $order) {
                $ordersCount++;
                $total = (int) ($order['total'] ?? 0);
                if ($total <= 0) {
                    $total = $this->sumLineItems($order);
                }
                $salesCents += $total;
                $tipsCents += (int) ($order['tipAmount'] ?? 0);
            }

            $offset += $pageSize;
        } while (count($orders) === $pageSize);

        return [
            'sales_total' => round($salesCents / 100, 2),
            'tips_total' => round($tipsCents / 100, 2),
            'orders_count' => $ordersCount,
        ];
    }

    private function resolveScope(array $item, array $scopeMap): array
    {
        $categories = Arr::get($item, 'categories.elements', []);

        foreach ($categories as $category) {
            $cloverId = $category['id'] ?? null;
            if (! $cloverId || ! isset($scopeMap[$cloverId])) {
                continue;
            }

            return [$scopeMap[$cloverId], $cloverId];
        }

        return [null, null];
    }

    private function resolveLocalCategory(string $scope, string $cloverId)
    {
        $cloverCategory = CloverCategory::where('clover_id', $cloverId)->first();
        $name = $cloverCategory?->name ?? 'Categoría';
        $order = $cloverCategory?->sort_order ?? 0;

        return match ($scope) {
            'menu' => $this->upsertCategory(Category::class, $cloverId, $name, $order),
            'cocktails' => $this->upsertCategory(CocktailCategory::class, $cloverId, $name, $order),
            'wines' => $this->upsertCategory(WineCategory::class, $cloverId, $name, $order),
            'cantina' => $this->upsertCategory(CantinaCategory::class, $cloverId, $name, $order),
            default => null,
        };
    }

    private function upsertCategory(string $modelClass, string $cloverId, string $name, int $order)
    {
        $category = $modelClass::firstOrCreate(
            ['clover_id' => $cloverId],
            ['name' => $name]
        );

        $dirty = false;
        if ($category->name !== $name) {
            $category->name = $name;
            $dirty = true;
        }

        if (Schema::hasColumn($category->getTable(), 'order') && $category->order !== $order) {
            $category->order = $order;
            $dirty = true;
        }

        if ($dirty) {
            $category->save();
        }

        return $category;
    }

    private function upsertItem(string $scope, array $payload)
    {
        $payload = $this->sanitizeItemPayload($scope, $payload);

        return match ($scope) {
            'menu' => Dish::updateOrCreate(['clover_id' => $payload['clover_id']], $payload),
            'cocktails' => Cocktail::updateOrCreate(['clover_id' => $payload['clover_id']], $payload),
            'wines' => Wine::updateOrCreate(['clover_id' => $payload['clover_id']], $payload),
            'cantina' => CantinaItem::updateOrCreate(['clover_id' => $payload['clover_id']], $payload),
            default => null,
        };
    }

    private function sanitizeItemPayload(string $scope, array $payload): array
    {
        $description = $payload['description'] ?? null;
        if ($scope === 'menu' && ($description === null || $description === '')) {
            $payload['description'] = '';
        }

        if (! Schema::hasColumn($this->resolveItemTable($scope), 'visible')) {
            unset($payload['visible']);
        }

        return $payload;
    }

    private function resolveItemTable(string $scope): string
    {
        return match ($scope) {
            'menu' => (new Dish())->getTable(),
            'cocktails' => (new Cocktail())->getTable(),
            'wines' => (new Wine())->getTable(),
            'cantina' => (new CantinaItem())->getTable(),
            default => (new Dish())->getTable(),
        };
    }

    private function resolveItemModelClass(string $scope): string
    {
        return match ($scope) {
            'menu' => Dish::class,
            'cocktails' => Cocktail::class,
            'wines' => Wine::class,
            'cantina' => CantinaItem::class,
            default => Dish::class,
        };
    }

    private function resolveDescription(array $item): ?string
    {
        $description = $item['description'] ?? null;
        if (! $description) {
            $description = $item['alternateName'] ?? null;
        }
        if (! $description) {
            $description = $item['onlineName'] ?? null;
        }
        if ($description === '') {
            $description = null;
        }

        return $description;
    }

    private function resolveQuantity(array $lineItem): float
    {
        if (isset($lineItem['quantity'])) {
            return (float) $lineItem['quantity'];
        }

        if (isset($lineItem['unitQty'])) {
            return ((float) $lineItem['unitQty']) / 1000;
        }

        return 1.0;
    }

    private function sumLineItems(array $order): int
    {
        $lineItems = Arr::get($order, 'lineItems.elements', []);
        $total = 0;

        foreach ($lineItems as $lineItem) {
            $price = (int) ($lineItem['price'] ?? 0);
            $quantity = $this->resolveQuantity($lineItem);
            $total += (int) round($price * $quantity);
        }

        return $total;
    }

    private function resolveVisibility(array $item): bool
    {
        $hidden = (bool) ($item['hidden'] ?? false);
        $available = (bool) ($item['available'] ?? true);
        $enabledOnline = (bool) ($item['enabledOnline'] ?? true);
        $deleted = (bool) ($item['deleted'] ?? false);

        return ! $hidden && $available && $enabledOnline && ! $deleted;
    }

    private function formatPrice($price): float
    {
        if ($price === null) {
            return 0.0;
        }

        return round(((float) $price) / 100, 2);
    }

    private function syncItemModifiers(string $scope, string $cloverItemId, $itemModel): void
    {
        if (! $itemModel) {
            return;
        }

        try {
            $payload = $this->client->listItemModifierGroups($cloverItemId);
        } catch (\RuntimeException $exception) {
            return;
        }
        $groups = Arr::get($payload, 'elements', []);

        if (! $groups) {
            $this->detachItemCloverExtras($itemModel);
            return;
        }

        $viewScope = $this->resolveViewScope($scope);
        $extraIds = [];

        foreach ($groups as $group) {
            $groupId = $group['id'] ?? null;
            $groupName = $group['name'] ?? null;
            if (! $groupId || ! $groupName) {
                continue;
            }

            // Clover fields vary by API version/account: support both naming styles.
            $minSelections = (int) ($group['minSelections'] ?? $group['minRequired'] ?? 0);
            $maxSelections = (int) ($group['maxSelections'] ?? $group['maxAllowed'] ?? 0);
            $groupRequired = $minSelections > 0;
            $maxSelect = $maxSelections > 0 ? $maxSelections : null;
            $minSelect = $minSelections > 0 ? $minSelections : null;
            $kind = ($groupRequired || $maxSelections === 1) ? 'modifier' : 'extra';

            $modifiers = Arr::get($group, 'modifiers.elements', []);
            foreach ($modifiers as $modifier) {
                $modifierId = $modifier['id'] ?? null;
                $modifierName = $modifier['name'] ?? null;
                if (! $modifierId || ! $modifierName) {
                    continue;
                }

                $price = $this->formatPrice($modifier['price'] ?? null);

                $extra = Extra::updateOrCreate(
                    ['clover_id' => $modifierId],
                    [
                        'name' => $modifierName,
                        'group_name' => $groupName,
                        'group_required' => $groupRequired,
                        'max_select' => $maxSelect,
                        'min_select' => $minSelect,
                        'kind' => $kind,
                        'price' => $price,
                        'description' => null,
                        'view_scope' => $viewScope,
                        'active' => true,
                        'clover_group_id' => $groupId,
                    ]
                );

                $extraIds[] = $extra->id;
            }
        }

        if ($extraIds !== []) {
            $itemModel->extras()->syncWithoutDetaching($extraIds);
            $this->detachItemCloverExtras($itemModel, $extraIds);
        }
    }

    private function detachItemCloverExtras($itemModel, array $keepIds = []): void
    {
        $assigned = $itemModel->extras()
            ->whereNotNull('extras.clover_group_id')
            ->pluck('extras.id')
            ->all();

        $removeIds = array_diff($assigned, $keepIds);
        if ($removeIds !== []) {
            $itemModel->extras()->detach($removeIds);
        }
    }

    private function resolveViewScope(string $scope): string
    {
        return match ($scope) {
            'wines' => 'coffee',
            default => $scope,
        };
    }

    private function deactivateMissingItems(string $scope, array $validCloverIds): void
    {
        $modelClass = $this->resolveItemModelClass($scope);
        $table = $this->resolveItemTable($scope);

        if (! Schema::hasColumn($table, 'visible')) {
            return;
        }

        $query = $modelClass::query()->whereNotNull('clover_id');
        if ($validCloverIds !== []) {
            $query->whereNotIn('clover_id', $validCloverIds);
        }

        $query->update(['visible' => false]);
    }
}
