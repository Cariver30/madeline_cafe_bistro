<?php

namespace App\Http\Controllers;

use App\Mail\PosReceiptMail;
use App\Models\CantinaCategory;
use App\Models\CantinaItem;
use App\Models\Category;
use App\Models\CocktailCategory;
use App\Models\Order;
use App\Models\OrderBatch;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\WineCategory;
use App\Support\CloverCheckoutClient;
use App\Support\CloverOrderService;
use App\Support\Orders\OnlineOrderService;
use App\Support\Orders\PosReceiptBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OnlineOrderController extends Controller
{
    public function show()
    {
        $settings = Setting::first();
        $onlineOrdering = $this->resolveOnlineOrderingStatus($settings);

        $dishQuery = function ($query) {
            $query->where('visible', true)
                ->with([
                    'subcategory:id,name,category_id',
                    'wines:id,name,price',
                    'cocktails:id,name,price',
                    'recommendedDishes:id,name,price',
                    'taxes:id,name,rate,active',
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

        $cocktailItemQuery = function ($query) {
            $query->where('visible', true)
                ->with([
                    'subcategory:id,name,cocktail_category_id',
                    'dishes:id,name,price',
                    'taxes:id,name,rate,active',
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

        $wineItemQuery = function ($query) {
            $query->where('visible', true)
                ->with([
                    'subcategory:id,name,wine_category_id',
                    'dishes:id,name,price',
                    'taxes:id,name,rate,active',
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

        $cantinaCategories = collect();
        if ($settings?->show_tab_cantina ?? true) {
            $cantinaItemQuery = function ($query) {
                $query->where('visible', true)
                    ->with([
                        'extras' => function ($extraQuery) {
                            $extraQuery->select('extras.id', 'name', 'group_name', 'group_required', 'max_select', 'min_select', 'kind', 'price', 'description', 'active');
                        },
                    ])
                    ->orderBy('position');
            };

            $cantinaCategories = CantinaCategory::with([
                    'items' => $cantinaItemQuery,
                ])
                ->orderBy('order')
                ->get();

            $cantinaCategories->each(function ($category) {
                $category->setAttribute('scope', 'cantina');
                $category->setAttribute('key', 'cantina-' . $category->id);
            });
        }

        $categories = $menuCategories
            ->concat($cocktailCategories)
            ->concat($wineCategories)
            ->concat($cantinaCategories)
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
            'orderMode' => true,
            'orderChannel' => 'online',
            'onlineOrdering' => $onlineOrdering,
        ]);
    }

    public function checkout(Request $request)
    {
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
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'pickup_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'items.required' => 'Debes agregar al menos un plato.',
            'customer_name.required' => 'Indica el nombre para la orden.',
            'pickup_at.required' => 'Selecciona la hora de recogido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa la orden enviada.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $settings = Setting::first();
        $onlineOrdering = $this->resolveOnlineOrderingStatus($settings);
        if (! $onlineOrdering['enabled']) {
            return response()->json([
                'message' => $onlineOrdering['message'],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $checkoutClient = CloverCheckoutClient::fromSettings($settings);
        if (! $checkoutClient) {
            return response()->json([
                'message' => 'Configura las credenciales de Clover antes de crear el checkout.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $validator->validated();
        $pickupAt = isset($data['pickup_at']) ? Carbon::parse($data['pickup_at']) : null;

        try {
            $service = new OnlineOrderService($checkoutClient);
            $result = $service->createCheckout(
                $data['items'],
                [
                    'name' => $data['customer_name'],
                    'email' => $data['customer_email'] ?? null,
                    'phone' => $data['customer_phone'] ?? null,
                ],
                $pickupAt,
                $data['notes'] ?? null,
            );
        } catch (Throwable $exception) {
            report($exception);

            $message = match ($exception->getMessage()) {
                'extras_required' => 'Selecciona las opciones requeridas antes de enviar.',
                'extras_max' => 'Superaste el máximo permitido en una opción.',
                'item_unavailable' => 'Uno de los productos ya no está disponible.',
                default => 'No se pudo crear el checkout.',
            };

            return response()->json([
                'message' => $message,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = $result['order'];
        $checkout = $result['checkout'];

        return response()->json([
            'message' => 'Checkout creado.',
            'order_id' => $order->id,
            'order_token' => $order->public_token,
            'checkout_id' => $checkout['id'] ?? null,
            'checkout_url' => $checkout['href'] ?? null,
            'checkout_page' => route('online.order.checkout.page', ['token' => $order->public_token]),
        ], Response::HTTP_CREATED);
    }

    public function checkoutPage(string $token)
    {
        $order = Order::where('public_token', $token)->firstOrFail();
        $settings = Setting::first();

        if (! $order->checkout_url) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return view('online.checkout', [
            'order' => $order,
            'checkoutUrl' => $order->checkout_url,
            'settings' => $settings,
        ]);
    }

    public function result(string $status, string $token)
    {
        $order = Order::where('public_token', $token)->firstOrFail();
        $status = strtolower($status);

        if (! in_array($status, ['success', 'failure', 'cancel'], true)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        if ($status === 'success') {
            $this->markOrderPaid($order);
        } elseif (in_array($order->payment_status, ['pending', null], true)) {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'payment_status' => $status === 'failure' ? 'failed' : 'cancelled',
                'checkout_status' => $status,
            ]);
        }

        return view('online.result', [
            'order' => $order->fresh(),
            'status' => $status,
            'settings' => Setting::first(),
        ]);
    }

    private function markOrderPaid(Order $order): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        $settings = Setting::first();
        $checkoutClient = CloverCheckoutClient::fromSettings($settings);
        $checkout = null;

        if ($checkoutClient && $order->checkout_id) {
            try {
                $checkout = $checkoutClient->getCheckout($order->checkout_id);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $totalCents = (int) data_get($checkout, 'shoppingCart.total', 0);
        if ($totalCents <= 0) {
            $totals = PosReceiptBuilder::calculateTotals($order);
            $totalCents = (int) round(($totals['total'] + 2.0) * 100);
        }

        $order->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'payment_status' => 'paid',
            'payment_method' => 'clover',
            'paid_at' => now(),
            'paid_total' => round($totalCents / 100, 2),
            'checkout_status' => 'paid',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'clover',
            'method' => 'online',
            'amount' => round($totalCents / 100, 2),
            'status' => 'paid',
            'meta' => $checkout,
        ]);

        $batch = $order->batches()->orderByDesc('id')->first();
        if ($batch) {
            $batch->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);
        }

        $this->sendReceiptMail($order);
        $this->sendToClover($order, $batch);
    }

    private function sendReceiptMail(Order $order): void
    {
        $email = trim((string) $order->customer_email);
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $receipt = PosReceiptBuilder::build($order, 'paid');
        $receiptUrl = URL::temporarySignedRoute(
            'receipts.pos.download',
            now()->addDays(2),
            ['order' => $order->id, 'stage' => 'paid'],
        );

        Mail::to($email)->send(
            new PosReceiptMail($order, $receipt, $receiptUrl, 'paid'),
        );
    }

    private function sendToClover(Order $order, ?OrderBatch $batch): void
    {
        if (! $batch || $batch->clover_order_id) {
            return;
        }

        $settings = Setting::first();
        $cloverService = CloverOrderService::fromSettings($settings);
        if (! $cloverService) {
            return;
        }

        try {
            $cloverResult = $cloverService->sendBatch($batch);
            $batch->update([
                'clover_order_id' => $cloverResult['order_id'] ?? null,
                'clover_print_event_id' => $cloverResult['print_event_id'] ?? null,
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function normalizeOrderItems(array $items): array
    {
        return array_map(function (array $item): array {
            $extras = $item['extras'] ?? [];
            if (! is_array($extras)) {
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

    private function resolveOnlineOrderingStatus(?Setting $settings, ?Carbon $now = null): array
    {
        $message = trim((string) ($settings?->online_pause_message ?? 'Por el momento no estamos tomando órdenes en línea.'));
        $enabledFlag = $settings?->online_enabled;

        if ($enabledFlag === null) {
            $enabledFlag = true;
        }

        if (! $enabledFlag) {
            return ['enabled' => false, 'message' => $message, 'reason' => 'paused'];
        }

        $schedule = $settings?->online_schedule;
        if (! is_array($schedule) || $schedule === []) {
            return ['enabled' => true, 'message' => $message, 'reason' => 'always'];
        }

        $now = $now ?: Carbon::now();
        $dayKey = strtolower($now->format('D'));
        $dayKey = substr($dayKey, 0, 3);
        $dayConfig = $schedule[$dayKey] ?? null;

        if (! is_array($dayConfig)) {
            return ['enabled' => true, 'message' => $message, 'reason' => 'no_schedule'];
        }

        if (! empty($dayConfig['closed'])) {
            return ['enabled' => false, 'message' => $message, 'reason' => 'closed_day'];
        }

        $start = $dayConfig['start'] ?? null;
        $end = $dayConfig['end'] ?? null;

        if (! $start || ! $end) {
            return ['enabled' => true, 'message' => $message, 'reason' => 'open'];
        }

        try {
            $startTime = $now->copy()->setTimeFromTimeString($start);
            $endTime = $now->copy()->setTimeFromTimeString($end);
        } catch (Throwable) {
            return ['enabled' => true, 'message' => $message, 'reason' => 'open'];
        }

        if ($endTime->lessThanOrEqualTo($startTime)) {
            $open = $now->greaterThanOrEqualTo($startTime) || $now->lessThanOrEqualTo($endTime);
        } else {
            $open = $now->betweenIncluded($startTime, $endTime);
        }

        return [
            'enabled' => $open,
            'message' => $message,
            'reason' => $open ? 'open' : 'closed_hours',
        ];
    }
}
