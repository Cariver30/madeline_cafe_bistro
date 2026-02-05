<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CocktailCategory;
use App\Models\OrderBatch;
use App\Models\TableSession;
use App\Models\CloverCategory;
use App\Models\WineCategory;
use App\Models\Setting;
use App\Support\Orders\TableOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class TableOrderController extends Controller
{
    public function show(string $token)
    {
        $session = TableSession::where('qr_token', $token)->firstOrFail();

        if ($session->status === 'closed') {
            return view('table-session-status', [
                'title' => 'Mesa cerrada',
                'message' => 'La mesa ya fue cerrada. Solicita un nuevo QR al mesero.',
            ]);
        }

        if ($session->expires_at && $session->expires_at->isPast()) {
            $session->update(['status' => 'expired']);
            return view('table-session-status', [
                'title' => 'QR expirado',
                'message' => 'Este código expiró. Pídele al mesero que lo renueve.',
            ]);
        }

        if ($session->status !== 'active') {
            return view('table-session-status', [
                'title' => 'QR no disponible',
                'message' => 'Este código no está disponible en este momento.',
            ]);
        }

        $orderMode = ($session->order_mode ?? 'table') === 'table';
        $settings = Setting::first();
        $cloverScopeMap = CloverCategory::select('clover_id', 'scope')
            ->get()
            ->keyBy('clover_id');
        $matchesScope = function ($category, string $scope) use ($cloverScopeMap): bool {
            if (empty($category->clover_id)) {
                return true;
            }

            return ($cloverScopeMap[$category->clover_id]->scope ?? null) === $scope;
        };

        $dishQuery = function ($query) {
            $query->where('visible', true)
                ->with([
                    'subcategory:id,name,category_id',
                    'wines:id,name,price',
                    'cocktails:id,name,price',
                    'recommendedDishes:id,name,price',
                    'extras' => function ($extraQuery) {
                        $extraQuery->select('extras.id', 'name', 'group_name', 'group_required', 'max_select', 'min_select', 'kind', 'price', 'description', 'active');
                    },
                ])
                ->orderBy('position');
        };

        $menuCategories = Category::with([
                'dishes' => $dishQuery,
                'subcategories' => function ($query) use ($dishQuery) {
                    $query->orderBy('order')
                        ->orderBy('id')
                        ->with(['dishes' => $dishQuery]);
                },
            ])
            ->orderBy('order')
            ->get();

        $menuCategories->each(function ($category) {
            $category->setAttribute('scope', 'menu');
            $category->setAttribute('key', 'menu-' . $category->id);
            $category->setRelation('items', $category->dishes);
        });
        $menuCategories = $menuCategories->filter(fn ($category) => $matchesScope($category, 'menu'));

        $cocktailItemQuery = function ($query) {
            $query->where('visible', true)
                ->with([
                    'subcategory:id,name,cocktail_category_id',
                    'dishes:id,name,price',
                    'extras' => function ($extraQuery) {
                        $extraQuery->select('extras.id', 'name', 'group_name', 'group_required', 'max_select', 'min_select', 'kind', 'price', 'description', 'active');
                    },
                ])
                ->orderBy('position');
        };

        $cocktailCategories = CocktailCategory::with([
                'items' => $cocktailItemQuery,
                'subcategories' => function ($query) use ($cocktailItemQuery) {
                    $query->orderBy('order')
                        ->orderBy('id')
                        ->with(['items' => $cocktailItemQuery]);
                },
            ])
            ->orderBy('order')
            ->get();

        $cocktailCategories->each(function ($category) {
            $category->setAttribute('scope', 'cocktails');
            $category->setAttribute('key', 'cocktails-' . $category->id);
        });
        $cocktailCategories = $cocktailCategories->filter(fn ($category) => $matchesScope($category, 'cocktails'));

        $wineItemQuery = function ($query) {
            $query->where('visible', true)
                ->with([
                    'subcategory:id,name,wine_category_id',
                    'dishes:id,name,price',
                    'extras' => function ($extraQuery) {
                        $extraQuery->select('extras.id', 'name', 'group_name', 'group_required', 'max_select', 'min_select', 'kind', 'price', 'description', 'active');
                    },
                ])
                ->orderBy('position');
        };

        $wineCategories = WineCategory::with([
                'items' => $wineItemQuery,
                'subcategories' => function ($query) use ($wineItemQuery) {
                    $query->orderBy('order')
                        ->orderBy('id')
                        ->with(['items' => $wineItemQuery]);
                },
            ])
            ->orderBy('order')
            ->get();

        $wineCategories->each(function ($category) {
            $category->setAttribute('scope', 'wines');
            $category->setAttribute('key', 'wines-' . $category->id);
        });
        $wineCategories = $wineCategories->filter(fn ($category) => $matchesScope($category, 'wines'));

        $categories = $menuCategories
            ->concat($cocktailCategories)
            ->concat($wineCategories)
            ->filter(function ($category) {
                $items = $category->items ?? $category->dishes ?? collect();
                if ($items->where('visible', true)->isNotEmpty()) {
                    return true;
                }

                $subcategories = $category->subcategories ?? collect();
                foreach ($subcategories as $subcategory) {
                    $subItems = $subcategory->items ?? $subcategory->dishes ?? collect();
                    if ($subItems->where('visible', true)->isNotEmpty()) {
                        return true;
                    }
                }

                return false;
            });

        return view('menu', [
            'settings' => $settings,
            'categories' => $categories,
            'orderMode' => $orderMode,
            'qrToken' => $session->qr_token,
            'tableSession' => $session,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $session = TableSession::where('qr_token', $token)->firstOrFail();

        if ($session->status !== 'active') {
            return response()->json([
                'message' => 'La mesa no está disponible.',
            ], Response::HTTP_GONE);
        }

        if ($session->expires_at && $session->expires_at->isPast()) {
            $session->update(['status' => 'expired']);
            return response()->json([
                'message' => 'El QR expiró. Solicita al mesero que lo renueve.',
            ], Response::HTTP_GONE);
        }

        if (($session->order_mode ?? 'table') !== 'table') {
            return response()->json([
                'message' => 'Esta mesa está en modo tradicional. Ordena con el mesero.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($session->open_order_id) {
            $pendingBatch = OrderBatch::where('order_id', $session->open_order_id)
                ->where('status', 'pending')
                ->orderByDesc('id')
                ->first();

            if ($pendingBatch) {
                return response()->json([
                    'message' => 'Tu orden anterior aún no ha sido confirmada por el mesero. Espera su aprobación para enviar otra.',
                    'pending_batch_id' => $pendingBatch->id,
                ], Response::HTTP_CONFLICT);
            }
        }

        $request->merge([
            'items' => $this->normalizeOrderItems($request->input('items', [])),
        ]);

        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'string', 'in:dish,cocktail,wine,cantina'],
            'items.*.id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
            'items.*.extras' => ['nullable', 'array'],
            'items.*.extras.*.id' => ['required', 'integer'],
            'items.*.extras.*.quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ], [
            'items.required' => 'Debes agregar al menos un plato.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa la orden enviada.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $batch = app(TableOrderService::class)->createBatch(
                $session,
                $validator->validated()['items'],
                'table',
            );
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'extras_required') {
                return response()->json([
                    'message' => 'Selecciona las opciones requeridas antes de enviar.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if ($e->getMessage() === 'extras_max') {
                return response()->json([
                    'message' => 'Superaste el máximo permitido en una opción.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if ($e->getMessage() === 'item_unavailable') {
                return response()->json([
                    'message' => 'Uno de los productos ya no está disponible.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            throw $e;
        }

        return response()->json([
            'message' => 'Orden enviada al mesero. Pendiente de confirmación.',
            'order_id' => $batch->order_id,
            'batch_id' => $batch->id,
        ], Response::HTTP_CREATED);
    }

    private function normalizeOrderItems(array $items): array
    {
        return array_map(function (array $item): array {
            $extras = $item['extras'] ?? [];
            if (!is_array($extras)) {
                $item['extras'] = [];
                return $item;
            }

            $normalized = [];
            foreach ($extras as $extra) {
                if (is_array($extra)) {
                    if (isset($extra['id'])) {
                        $normalized[] = [
                            'id' => (int) $extra['id'],
                            'quantity' => $extra['quantity'] ?? null,
                        ];
                        continue;
                    }
                    if (isset($extra['extra_id'])) {
                        $normalized[] = [
                            'id' => (int) $extra['extra_id'],
                            'quantity' => $extra['quantity'] ?? null,
                        ];
                    }
                    continue;
                }

                if (is_numeric($extra)) {
                    $normalized[] = ['id' => (int) $extra];
                }
            }

            $item['extras'] = $normalized;

            return $item;
        }, $items);
    }
}
