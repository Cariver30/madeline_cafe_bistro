<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Extra;
use App\Models\Cocktail;
use App\Models\CocktailCategory;
use App\Models\Wine;
use App\Models\WineCategory;
use App\Models\CantinaCategory;
use App\Models\CantinaItem;
use App\Models\Setting;
use App\Models\Popup; // Añadir el modelo Popup
use App\Models\WineType;
use App\Models\DiningTable;
use App\Models\Region;
use App\Models\Grape;
use App\Models\FoodPairing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TableSession;
use App\Support\FeaturedGroupBuilder;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyCustomer;
use App\Models\User;
use App\Models\Printer;
use App\Models\PrintTemplate;
use App\Models\PrinterRoute;
use App\Models\PrepArea;
use App\Models\PrepLabel;
use App\Models\Tax;

use App\Support\CloverClient;
use App\Support\CloverSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;


class AdminController extends Controller
{
    protected function sanitizeLabel(?string $value, ?string $fallback = null): ?string
    {
        $value = is_string($value) ? trim($value) : null;
        if ($value === null || $value === '') {
            return $fallback;
        }

        return $value;
    }

    protected function resolveTabLabel(?string $value, ?string $fallback): ?string
    {
        $value = is_string($value) ? trim($value) : null;
        if ($value === '') {
            return null;
        }

        return $value ?? $fallback;
    }

    protected function parseTipPresets(?string $value): array
    {
        if (!is_string($value)) {
            return [];
        }

        $value = trim($value);
        if ($value === '') {
            return [];
        }

        $parsed = null;
        if (str_starts_with($value, '[')) {
            $parsed = json_decode($value, true);
        }

        $candidates = is_array($parsed) ? $parsed : preg_split('/[\s,]+/', $value);

        $numbers = array_values(array_filter(array_map(function ($item) {
            if (!is_numeric($item)) {
                return null;
            }
            $value = (float) $item;
            if ($value <= 0 || $value > 100) {
                return null;
            }
            return round($value, 2);
        }, $candidates ?? [])));

        return array_values(array_unique($numbers));
    }

    public function panel()
    {
        $categories = Category::with(['dishes.subcategory', 'subcategories'])->orderBy('order')->get();
        $dishes = Dish::with('category')->get();
        $cocktails = Cocktail::with(['category', 'subcategory'])->get();
        $cocktailCategories = CocktailCategory::with(['items.subcategory', 'subcategories'])->orderBy('order')->get();
        $cantinaItems = CantinaItem::with('category')->get();
        $cantinaCategories = CantinaCategory::with('items')->orderBy('order')->get();
        $wines = Wine::with(['category', 'subcategory'])->get();
        $wineCategories = WineCategory::with(['items.subcategory', 'subcategories'])->orderBy('order')->get();
        $settings = Setting::first();
        $popups = Popup::all(); // Asegúrate de obtener todos los popups


        $managers = User::where('role', 'manager')->orderBy('name')->get();

        return view('admin', compact('categories', 'dishes', 'cocktails', 'cocktailCategories', 'cantinaItems', 'cantinaCategories', 'wines', 'wineCategories', 'settings', 'popups', 'managers'));
    }

    public function newAdminPanel()
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $categories = Category::with(['dishes.subcategory', 'subcategories'])->orderBy('order')->get();
        $dishes = Dish::with('category')->get();
        $cocktails = Cocktail::with(['category', 'subcategory'])->get();
        $cocktailCategories = CocktailCategory::with(['items.subcategory', 'subcategories'])->orderBy('order')->get();
        $cantinaItems = CantinaItem::with('category')->get();
        $cantinaCategories = CantinaCategory::with('items')->orderBy('order')->get();
        $wines = Wine::with(['category', 'subcategory'])->get();
        $wineCategories = WineCategory::with(['items.subcategory', 'subcategories'])->orderBy('order')->get();
        $settings = Setting::first();
        $popups = Popup::all(); // Asegúrate de obtener todos los popups
        $wineTypes = WineType::all();
$regions = Region::all();
$grapes = Grape::all();
$foodPairings = FoodPairing::all();
        $extras = Extra::orderBy('name')->get();
        $taxes = Tax::orderBy('name')->get();
        $featuredGroups = FeaturedGroupBuilder::build(true);
        $loyaltyRewards = LoyaltyReward::orderBy('points_required')->get();
        $staffUsers = User::whereIn('role', ['server', 'pos', 'host'])
            ->orderBy('role')
            ->orderBy('name')
            ->get();
        $managers = User::where('role', 'manager')->orderBy('name')->get();
        $loyaltyCustomers = LoyaltyCustomer::orderByDesc('points')->limit(8)->get();
        $loyaltyCustomerCount = LoyaltyCustomer::count();
        $printers = Printer::orderBy('name')->get();
        $printTemplates = PrintTemplate::orderBy('name')->get();
        $printerRoutes = PrinterRoute::with(['printer', 'template'])->orderBy('printer_id')->get();
        $prepAreas = PrepArea::with(['labels' => function ($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get();
        $prepLabels = PrepLabel::with(['area', 'printer'])->orderBy('name')->get();

        $today = Carbon::today();
        $todayEnd = $today->copy()->endOfDay();
        $yesterday = $today->copy()->subDay();
        $yesterdayEnd = $yesterday->copy()->endOfDay();
        $weekStart = $today->copy()->subDays(6);
        $weekEnd = $todayEnd;
        $prevWeekStart = $today->copy()->subDays(13);
        $prevWeekEnd = $today->copy()->subDays(7)->endOfDay();

        $ordersToday = Order::where('status', 'confirmed')
            ->where(function ($query) use ($today, $todayEnd) {
                $query
                    ->whereBetween('paid_at', [$today, $todayEnd])
                    ->orWhereBetween('confirmed_at', [$today, $todayEnd]);
            });

        $salesTotal = (float) (clone $ordersToday)->sum(DB::raw('COALESCE(paid_total, 0)'));
        $tipsTotal = (float) (clone $ordersToday)->sum(DB::raw('COALESCE(tip_total, 0)'));
        $ordersCount = (int) (clone $ordersToday)->count();

        $salesYesterday = (float) Order::where('status', 'confirmed')
            ->where(function ($query) use ($yesterday, $yesterdayEnd) {
                $query
                    ->whereBetween('paid_at', [$yesterday, $yesterdayEnd])
                    ->orWhereBetween('confirmed_at', [$yesterday, $yesterdayEnd]);
            })
            ->sum(DB::raw('COALESCE(paid_total, 0)'));

        $salesDeltaPercent = null;
        if ($salesYesterday > 0) {
            $salesDeltaPercent = (($salesTotal - $salesYesterday) / $salesYesterday) * 100;
        }

        $salesWeekTotal = (float) Order::where('status', 'confirmed')
            ->where(function ($query) use ($weekStart, $weekEnd) {
                $query
                    ->whereBetween('paid_at', [$weekStart, $weekEnd])
                    ->orWhereBetween('confirmed_at', [$weekStart, $weekEnd]);
            })
            ->sum(DB::raw('COALESCE(paid_total, 0)'));

        $salesPrevWeek = (float) Order::where('status', 'confirmed')
            ->where(function ($query) use ($prevWeekStart, $prevWeekEnd) {
                $query
                    ->whereBetween('paid_at', [$prevWeekStart, $prevWeekEnd])
                    ->orWhereBetween('confirmed_at', [$prevWeekStart, $prevWeekEnd]);
            })
            ->sum(DB::raw('COALESCE(paid_total, 0)'));

        $salesWeekDelta = null;
        if ($salesPrevWeek > 0) {
            $salesWeekDelta = (($salesWeekTotal - $salesPrevWeek) / $salesPrevWeek) * 100;
        }

        $openTables = (int) TableSession::whereIn('status', ['active', 'expired'])->count();
        $openTickets = (int) TableSession::whereIn('status', ['active', 'expired'])
            ->whereIn('service_channel', ['walkin', 'phone'])
            ->count();

        $voidedTotal = (float) OrderItem::whereNotNull('voided_at')
            ->whereBetween('voided_at', [$today, $todayEnd])
            ->sum(DB::raw('quantity * unit_price'));

        $activeTableCounts = TableSession::selectRaw('server_id, COUNT(*) as total')
            ->whereIn('status', ['active', 'expired'])
            ->groupBy('server_id')
            ->pluck('total', 'server_id');

        $openOrderCounts = Order::selectRaw('server_id, COUNT(*) as total')
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereNull('paid_at')
            ->groupBy('server_id')
            ->pluck('total', 'server_id');

        $serverTotals = Order::selectRaw('server_id, SUM(COALESCE(paid_total, 0)) as sales_total, SUM(COALESCE(tip_total, 0)) as tips_total, COUNT(*) as orders_count')
            ->where('status', 'confirmed')
            ->where(function ($query) use ($today, $todayEnd) {
                $query
                    ->whereBetween('paid_at', [$today, $todayEnd])
                    ->orWhereBetween('confirmed_at', [$today, $todayEnd]);
            })
            ->groupBy('server_id')
            ->get()
            ->keyBy('server_id');

        $opsSalesByChannel = Order::selectRaw('table_sessions.service_channel as channel, SUM(COALESCE(orders.paid_total, 0)) as sales_total, COUNT(*) as orders_count')
            ->join('table_sessions', 'table_sessions.id', '=', 'orders.table_session_id')
            ->where('orders.status', 'confirmed')
            ->where(function ($query) use ($today, $todayEnd) {
                $query
                    ->whereBetween('orders.paid_at', [$today, $todayEnd])
                    ->orWhereBetween('orders.confirmed_at', [$today, $todayEnd]);
            })
            ->groupBy('table_sessions.service_channel')
            ->orderByDesc('sales_total')
            ->get()
            ->map(fn ($row) => [
                'channel' => $row->channel ?: 'table',
                'sales_total' => round((float) ($row->sales_total ?? 0), 2),
                'orders_count' => (int) ($row->orders_count ?? 0),
            ])
            ->values()
            ->all();

        $opsTopItems = OrderItem::selectRaw('name, SUM(quantity) as quantity, SUM(quantity * unit_price) as revenue')
            ->whereNull('voided_at')
            ->whereHas('order', function ($query) use ($today, $todayEnd) {
                $query
                    ->where('status', 'confirmed')
                    ->where(function ($query) use ($today, $todayEnd) {
                        $query
                            ->whereBetween('paid_at', [$today, $todayEnd])
                            ->orWhereBetween('confirmed_at', [$today, $todayEnd]);
                    });
            })
            ->groupBy('name')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'quantity' => (int) ($row->quantity ?? 0),
                'revenue' => round((float) ($row->revenue ?? 0), 2),
            ])
            ->values()
            ->all();

        $opsTotals = [
            'sales_total' => round($salesTotal, 2),
            'tips_total' => round($tipsTotal, 2),
            'orders_count' => $ordersCount,
            'open_tables' => $openTables,
            'open_tickets' => $openTickets,
            'voided_total' => round($voidedTotal, 2),
            'sales_total_yesterday' => round($salesYesterday, 2),
            'sales_delta_percent' => $salesDeltaPercent !== null ? round($salesDeltaPercent, 1) : null,
            'sales_week_total' => round($salesWeekTotal, 2),
            'sales_week_prev' => round($salesPrevWeek, 2),
            'sales_week_delta_percent' => $salesWeekDelta !== null ? round($salesWeekDelta, 1) : null,
        ];

        $opsServers = User::where('role', 'server')
            ->orderBy('name')
            ->get()
            ->map(function (User $user) use ($activeTableCounts, $openOrderCounts, $serverTotals) {
                $stats = $serverTotals->get($user->id);
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'active' => (bool) $user->active,
                    'is_online' => !is_null($user->api_token),
                    'last_seen_at' => $user->updated_at?->toIso8601String(),
                    'active_tables' => (int) ($activeTableCounts[$user->id] ?? 0),
                    'open_orders' => (int) ($openOrderCounts[$user->id] ?? 0),
                    'sales_total' => round((float) ($stats->sales_total ?? 0), 2),
                    'tips_total' => round((float) ($stats->tips_total ?? 0), 2),
                    'orders_count' => (int) ($stats->orders_count ?? 0),
                ];
            });

        $cloverClient = CloverClient::fromSettings($settings);
        // Only fetch Clover metrics when explicitly requested to avoid blocking the admin panel.
        $cloverLiveMetrics = (bool) config('services.clover.live_metrics', false)
            && request()->boolean('clover_live');
        if ($cloverClient && $cloverLiveMetrics) {
            try {
                $cloverService = new CloverSyncService($cloverClient);
                $todaySummary = $cloverService->rangeSummary($today, $todayEnd);
                $yesterdaySummary = $cloverService->rangeSummary($yesterday, $yesterdayEnd);
                $weekSummary = $cloverService->rangeSummary($weekStart, $weekEnd);
                $prevWeekSummary = $cloverService->rangeSummary($prevWeekStart, $prevWeekEnd);

                $salesDeltaPercent = null;
                if (($yesterdaySummary['sales_total'] ?? 0) > 0) {
                    $salesDeltaPercent = (($todaySummary['sales_total'] - $yesterdaySummary['sales_total']) / $yesterdaySummary['sales_total']) * 100;
                }

                $salesWeekDelta = null;
                if (($prevWeekSummary['sales_total'] ?? 0) > 0) {
                    $salesWeekDelta = (($weekSummary['sales_total'] - $prevWeekSummary['sales_total']) / $prevWeekSummary['sales_total']) * 100;
                }

                $opsTotals = [
                    'sales_total' => round((float) ($todaySummary['sales_total'] ?? 0), 2),
                    'tips_total' => round((float) ($todaySummary['tips_total'] ?? 0), 2),
                    'orders_count' => (int) ($todaySummary['orders_count'] ?? 0),
                    'open_tables' => 0,
                    'open_tickets' => 0,
                    'voided_total' => 0,
                    'sales_total_yesterday' => round((float) ($yesterdaySummary['sales_total'] ?? 0), 2),
                    'sales_delta_percent' => $salesDeltaPercent !== null ? round($salesDeltaPercent, 1) : null,
                    'sales_week_total' => round((float) ($weekSummary['sales_total'] ?? 0), 2),
                    'sales_week_prev' => round((float) ($prevWeekSummary['sales_total'] ?? 0), 2),
                    'sales_week_delta_percent' => $salesWeekDelta !== null ? round($salesWeekDelta, 1) : null,
                ];

                $opsSalesByChannel = [
                    [
                        'channel' => 'clover',
                        'sales_total' => round((float) ($todaySummary['sales_total'] ?? 0), 2),
                        'orders_count' => (int) ($todaySummary['orders_count'] ?? 0),
                    ],
                ];

                $opsTopItems = $cloverService->topSellers($today, $todayEnd, 5);
            } catch (\Throwable $exception) {
                // fallback to local ops data if Clover is not reachable
            }
        }



        $diningTables = \Illuminate\Support\Facades\Schema::hasTable('dining_tables')
            ? DiningTable::orderBy('position')->orderBy('label')->get()
            : collect();

        return view('admin.admin-panel', compact(
            'categories',
            'dishes',
            'cocktails',
            'cocktailCategories',
            'cantinaItems',
            'cantinaCategories',
            'wines',
            'wineCategories',
            'settings',
            'popups',
            'wineTypes',
            'regions',
            'grapes',
            'foodPairings',
            'featuredGroups',
            'extras',
            'taxes',
            'loyaltyRewards',
            'staffUsers',
            'loyaltyCustomers',
            'loyaltyCustomerCount',
            'managers',
            'printers',
            'printTemplates',
            'printerRoutes',
            'prepAreas',
            'prepLabels',
            'opsTotals',
            'opsServers',
            'opsSalesByChannel',
            'opsTopItems',
            'diningTables'
        ));
    }

    public function updateBackground(Request $request)
    {
        $request->validate([
            'background_image_cover' => 'nullable|image',
            'background_image_menu' => 'nullable|image',
            'background_image_cocktails' => 'nullable|image',
            'background_image_wines' => 'nullable|image',
            'background_image_cantina' => 'nullable|image',
            'background_image_specials' => 'nullable|image',
            'logo' => 'nullable|image',
            'text_color_cover' => 'nullable|string',
            'text_color_menu' => 'nullable|string',
            'text_color_cocktails' => 'nullable|string',
            'text_color_wines' => 'nullable|string',
            'text_color_cantina' => 'nullable|string',
            'text_color_specials' => 'nullable|string',
            'card_opacity_cover' => 'nullable|numeric|between:0,1',
            'card_opacity_menu' => 'nullable|numeric|between:0,1',
            'card_opacity_cocktails' => 'nullable|numeric|between:0,1',
            'card_opacity_wines' => 'nullable|numeric|between:0,1',
            'card_opacity_cantina' => 'nullable|numeric|between:0,1',
            'card_opacity_specials' => 'nullable|numeric|between:0,1',
            'font_family_cover' => 'nullable|string',
            'font_family_menu' => 'nullable|string',
            'font_family_cocktails' => 'nullable|string',
            'font_family_wines' => 'nullable|string',
            'font_family_cantina' => 'nullable|string',
            'font_family_specials' => 'nullable|string',
            'button_color_cover' => 'nullable|string',
            'button_color_menu' => 'nullable|string',
            'button_color_cocktails' => 'nullable|string',
            'button_color_wines' => 'nullable|string',
            'button_color_cantina' => 'nullable|string',
            'button_color_specials' => 'nullable|string',
            'button_font_size_cover' => 'nullable|integer',
            'category_name_bg_color_menu' => 'nullable|string',
            'category_name_text_color_menu' => 'nullable|string',
            'category_name_font_size_menu' => 'nullable|integer',
            'subcategory_name_bg_color_menu' => 'nullable|string',
            'subcategory_name_text_color_menu' => 'nullable|string',
            'category_name_bg_color_cocktails' => 'nullable|string',
            'category_name_text_color_cocktails' => 'nullable|string',
            'category_name_font_size_cocktails' => 'nullable|integer',
            'subcategory_name_bg_color_cocktails' => 'nullable|string',
            'subcategory_name_text_color_cocktails' => 'nullable|string',
            'category_name_bg_color_wines' => 'nullable|string',
            'category_name_text_color_wines' => 'nullable|string',
            'category_name_font_size_wines' => 'nullable|integer',
            'subcategory_name_bg_color_wines' => 'nullable|string',
            'subcategory_name_text_color_wines' => 'nullable|string',
            'category_name_bg_color_cantina' => 'nullable|string',
            'category_name_text_color_cantina' => 'nullable|string',
            'category_name_font_size_cantina' => 'nullable|integer',
            'category_name_bg_color_specials' => 'nullable|string',
            'category_name_text_color_specials' => 'nullable|string',
            'category_name_font_size_specials' => 'nullable|integer',
            'card_bg_color_menu' => 'nullable|string',
            'card_bg_color_cocktails' => 'nullable|string',
            'card_bg_color_wines' => 'nullable|string',
            'card_bg_color_cantina' => 'nullable|string',
            'card_bg_color_specials' => 'nullable|string',
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'phone_number' => 'nullable|string',
            'business_hours' => 'nullable|string',
            'online_enabled' => 'nullable|boolean',
            'online_pause_message' => 'nullable|string|max:255',
            'online_schedule' => 'nullable|array',
            'online_schedule.*.start' => 'nullable|date_format:H:i',
            'online_schedule.*.end' => 'nullable|date_format:H:i',
            'online_schedule.*.closed' => 'nullable|boolean',
            'fixed_bottom_font_size' => 'nullable|integer',
            'fixed_bottom_font_color' => 'nullable|string',
            'button_label_menu' => 'nullable|string|max:255',
            'button_label_cocktails' => 'nullable|string|max:255',
            'button_label_wines' => 'nullable|string|max:255',
            'button_label_cantina' => 'nullable|string|max:255',
            'button_label_events' => 'nullable|string|max:255',
            'button_label_vip' => 'nullable|string|max:255',
            'button_label_reservations' => 'nullable|string|max:255',
            'button_label_online' => 'nullable|string|max:255',
            'button_label_specials' => 'nullable|string|max:255',
            'cta_image_online' => 'nullable|image',
            'cta_image_specials' => 'nullable|image',
            'cover_cta_online_bg_color' => 'nullable|string',
            'cover_cta_online_text_color' => 'nullable|string',
            'cover_cta_specials_bg_color' => 'nullable|string',
            'cover_cta_specials_text_color' => 'nullable|string',
            'show_cta_online' => 'nullable|boolean',
            'show_cta_specials' => 'nullable|boolean',
            'cover_cta_position' => 'nullable|array',
            'cover_cta_position.*' => 'nullable|integer|min:1|max:99',
            'cover_cta_target' => 'nullable|array',
            'cover_cta_target.*' => 'nullable|string|in:menu,online,cafe,cocktails,cantina,specials,events,reservations,vip',
            'tab_label_menu' => 'nullable|string|max:255',
            'tab_label_cocktails' => 'nullable|string|max:255',
            'tab_label_wines' => 'nullable|string|max:255',
            'tab_label_cantina' => 'nullable|string|max:255',
            'tab_label_events' => 'nullable|string|max:255',
            'tab_label_loyalty' => 'nullable|string|max:255',
            'tip_presets' => 'nullable|string',
            'tip_allow_custom' => 'nullable|boolean',
            'tip_allow_skip' => 'nullable|boolean',
            'mobile_ip_restriction_enabled' => 'nullable|boolean',
            'mobile_ip_allowlist' => 'nullable|string',
            'mobile_ip_bypass_emails' => 'nullable|string',
            'clover_merchant_id' => 'nullable|string|max:255',
            'clover_access_token' => 'nullable|string|max:2048',
            'clover_env' => 'nullable|string|in:production,sandbox',
            'clover_device_host' => 'nullable|string|max:255',
            'clover_device_token' => 'nullable|string|max:2048',
            'clover_clear_token' => 'nullable|boolean',
        ]);

        $settings = Setting::first();

        $buttonLabelMenu = $this->sanitizeLabel($request->input('button_label_menu', $settings->button_label_menu));
        $buttonLabelCocktails = $this->sanitizeLabel($request->input('button_label_cocktails', $settings->button_label_cocktails));
        $buttonLabelWines = $this->sanitizeLabel($request->input('button_label_wines', $settings->button_label_wines));
        $buttonLabelCantina = $this->sanitizeLabel($request->input('button_label_cantina', $settings->button_label_cantina));
        $buttonLabelEvents = $this->sanitizeLabel($request->input('button_label_events', $settings->button_label_events));
        $buttonLabelOnline = $this->sanitizeLabel($request->input('button_label_online', $settings->button_label_online));
        $buttonLabelSpecials = $this->sanitizeLabel($request->input('button_label_specials', $settings->button_label_specials));

        if ($request->hasFile('background_image_cover')) {
            $path = $request->file('background_image_cover')->store('background_images', 'public');
            $settings->background_image_cover = $path;
        }
        if ($request->hasFile('background_image_menu')) {
            $path = $request->file('background_image_menu')->store('background_images', 'public');
            $settings->background_image_menu = $path;
        }
        if ($request->hasFile('background_image_cocktails')) {
            $path = $request->file('background_image_cocktails')->store('background_images', 'public');
            $settings->background_image_cocktails = $path;
        }
        if ($request->hasFile('background_image_wines')) {
            $path = $request->file('background_image_wines')->store('background_images', 'public');
            $settings->background_image_wines = $path;
        }
        if ($request->hasFile('background_image_cantina')) {
            $path = $request->file('background_image_cantina')->store('background_images', 'public');
            $settings->background_image_cantina = $path;
        }
        if ($request->hasFile('background_image_specials')) {
            $path = $request->file('background_image_specials')->store('background_images', 'public');
            $settings->background_image_specials = $path;
        }
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $settings->logo = $path;
        }

        if ($request->boolean('remove_menu_hero_image')) {
            if ($settings->menu_hero_image) {
                Storage::disk('public')->delete($settings->menu_hero_image);
            }
            $settings->menu_hero_image = null;
        }
        if ($request->boolean('remove_cocktail_hero_image')) {
            if ($settings->cocktail_hero_image) {
                Storage::disk('public')->delete($settings->cocktail_hero_image);
            }
            $settings->cocktail_hero_image = null;
        }
        if ($request->boolean('remove_coffee_hero_image')) {
            if ($settings->coffee_hero_image) {
                Storage::disk('public')->delete($settings->coffee_hero_image);
            }
            $settings->coffee_hero_image = null;
        }

        if ($request->hasFile('menu_hero_image')) {
            $settings->menu_hero_image = $request->file('menu_hero_image')->store('hero_images', 'public');
        }
        if ($request->hasFile('cocktail_hero_image')) {
            $settings->cocktail_hero_image = $request->file('cocktail_hero_image')->store('hero_images', 'public');
        }
        if ($request->hasFile('coffee_hero_image')) {
            $settings->coffee_hero_image = $request->file('coffee_hero_image')->store('hero_images', 'public');
        }
        if ($request->hasFile('cta_image_menu')) {
            $settings->cta_image_menu = $request->file('cta_image_menu')->store('cta_images', 'public');
        }
        if ($request->hasFile('cta_image_cafe')) {
            $settings->cta_image_cafe = $request->file('cta_image_cafe')->store('cta_images', 'public');
        }
        if ($request->hasFile('cta_image_cocktails')) {
            $settings->cta_image_cocktails = $request->file('cta_image_cocktails')->store('cta_images', 'public');
        }
        if ($request->hasFile('cta_image_cantina')) {
            $settings->cta_image_cantina = $request->file('cta_image_cantina')->store('cta_images', 'public');
        }
        if ($request->hasFile('cta_image_events')) {
            $settings->cta_image_events = $request->file('cta_image_events')->store('cta_images', 'public');
        }
        if ($request->hasFile('cta_image_reservations')) {
            $settings->cta_image_reservations = $request->file('cta_image_reservations')->store('cta_images', 'public');
        }
        if ($request->hasFile('cta_image_online')) {
            $settings->cta_image_online = $request->file('cta_image_online')->store('cta_images', 'public');
        }
        if ($request->hasFile('cta_image_specials')) {
            $settings->cta_image_specials = $request->file('cta_image_specials')->store('cta_images', 'public');
        }

        $settings->text_color_cover = $request->input('text_color_cover', $settings->text_color_cover);
        if (Schema::hasColumn('settings', 'text_color_cover_secondary')) {
            $settings->text_color_cover_secondary = $request->input('text_color_cover_secondary', $settings->text_color_cover_secondary);
        }
        if (Schema::hasColumn('settings', 'cover_hero_kicker')) {
            $settings->cover_hero_kicker = $request->input('cover_hero_kicker', $settings->cover_hero_kicker);
            $settings->cover_hero_title = $request->input('cover_hero_title', $settings->cover_hero_title);
            $settings->cover_hero_paragraph = $request->input('cover_hero_paragraph', $settings->cover_hero_paragraph);
            $settings->cover_location_text = $request->input('cover_location_text', $settings->cover_location_text);
            $settings->cover_cta_menu_bg_color = $request->input('cover_cta_menu_bg_color', $settings->cover_cta_menu_bg_color);
            $settings->cover_cta_menu_text_color = $request->input('cover_cta_menu_text_color', $settings->cover_cta_menu_text_color);
            $settings->cover_cta_cafe_bg_color = $request->input('cover_cta_cafe_bg_color', $settings->cover_cta_cafe_bg_color);
            $settings->cover_cta_cafe_text_color = $request->input('cover_cta_cafe_text_color', $settings->cover_cta_cafe_text_color);
            $settings->cover_cta_cocktails_bg_color = $request->input('cover_cta_cocktails_bg_color', $settings->cover_cta_cocktails_bg_color);
            $settings->cover_cta_cocktails_text_color = $request->input('cover_cta_cocktails_text_color', $settings->cover_cta_cocktails_text_color);
            $settings->cover_cta_cantina_bg_color = $request->input('cover_cta_cantina_bg_color', $settings->cover_cta_cantina_bg_color);
            $settings->cover_cta_cantina_text_color = $request->input('cover_cta_cantina_text_color', $settings->cover_cta_cantina_text_color);
            $settings->cover_cta_events_bg_color = $request->input('cover_cta_events_bg_color', $settings->cover_cta_events_bg_color);
            $settings->cover_cta_events_text_color = $request->input('cover_cta_events_text_color', $settings->cover_cta_events_text_color);
            $settings->cover_cta_reservations_bg_color = $request->input('cover_cta_reservations_bg_color', $settings->cover_cta_reservations_bg_color);
            $settings->cover_cta_reservations_text_color = $request->input('cover_cta_reservations_text_color', $settings->cover_cta_reservations_text_color);
            $settings->cover_cta_online_bg_color = $request->input('cover_cta_online_bg_color', $settings->cover_cta_online_bg_color);
            $settings->cover_cta_online_text_color = $request->input('cover_cta_online_text_color', $settings->cover_cta_online_text_color);
            $settings->cover_cta_vip_bg_color = $request->input('cover_cta_vip_bg_color', $settings->cover_cta_vip_bg_color);
            $settings->cover_cta_vip_text_color = $request->input('cover_cta_vip_text_color', $settings->cover_cta_vip_text_color);
            $settings->cover_cta_specials_bg_color = $request->input('cover_cta_specials_bg_color', $settings->cover_cta_specials_bg_color);
            $settings->cover_cta_specials_text_color = $request->input('cover_cta_specials_text_color', $settings->cover_cta_specials_text_color);
        }
        $settings->text_color_menu = $request->input('text_color_menu', $settings->text_color_menu);
        $settings->text_color_cocktails = $request->input('text_color_cocktails', $settings->text_color_cocktails);
        $settings->text_color_wines = $request->input('text_color_wines', $settings->text_color_wines);
        $settings->text_color_cantina = $request->input('text_color_cantina', $settings->text_color_cantina);
        $settings->text_color_specials = $request->input('text_color_specials', $settings->text_color_specials);

        $settings->card_opacity_cover = $request->input('card_opacity_cover', $settings->card_opacity_cover);
        $settings->card_opacity_menu = $request->input('card_opacity_menu', $settings->card_opacity_menu);
        $settings->card_opacity_cocktails = $request->input('card_opacity_cocktails', $settings->card_opacity_cocktails);
        $settings->card_opacity_wines = $request->input('card_opacity_wines', $settings->card_opacity_wines);
        $settings->card_opacity_cantina = $request->input('card_opacity_cantina', $settings->card_opacity_cantina);
        $settings->card_opacity_specials = $request->input('card_opacity_specials', $settings->card_opacity_specials);

        $settings->font_family_cover = $request->input('font_family_cover', $settings->font_family_cover);
        $settings->font_family_menu = $request->input('font_family_menu', $settings->font_family_menu);
        $settings->font_family_cocktails = $request->input('font_family_cocktails', $settings->font_family_cocktails);
        $settings->font_family_wines = $request->input('font_family_wines', $settings->font_family_wines);
        $settings->font_family_cantina = $request->input('font_family_cantina', $settings->font_family_cantina);
        $settings->font_family_specials = $request->input('font_family_specials', $settings->font_family_specials);

        $settings->button_color_cover = $request->input('button_color_cover', $settings->button_color_cover);
        $settings->card_bg_color_cover = $request->input('card_bg_color_cover', $settings->card_bg_color_cover);
        $settings->button_color_menu = $request->input('button_color_menu', $settings->button_color_menu);
        $settings->button_color_cocktails = $request->input('button_color_cocktails', $settings->button_color_cocktails);
        $settings->button_color_wines = $request->input('button_color_wines', $settings->button_color_wines);
        $settings->button_color_cantina = $request->input('button_color_cantina', $settings->button_color_cantina);
        $settings->button_color_specials = $request->input('button_color_specials', $settings->button_color_specials);

        $settings->button_font_size_cover = $request->input('button_font_size_cover', $settings->button_font_size_cover);

        $settings->category_name_bg_color_menu = $request->input('category_name_bg_color_menu', $settings->category_name_bg_color_menu);
        $settings->category_name_text_color_menu = $request->input('category_name_text_color_menu', $settings->category_name_text_color_menu);
        $settings->category_name_font_size_menu = $request->input('category_name_font_size_menu', $settings->category_name_font_size_menu);
        $settings->subcategory_name_bg_color_menu = $request->input('subcategory_name_bg_color_menu', $settings->subcategory_name_bg_color_menu);
        $settings->subcategory_name_text_color_menu = $request->input('subcategory_name_text_color_menu', $settings->subcategory_name_text_color_menu);

        $settings->category_name_bg_color_cocktails = $request->input('category_name_bg_color_cocktails', $settings->category_name_bg_color_cocktails);
        $settings->category_name_text_color_cocktails = $request->input('category_name_text_color_cocktails', $settings->category_name_text_color_cocktails);
        $settings->category_name_font_size_cocktails = $request->input('category_name_font_size_cocktails', $settings->category_name_font_size_cocktails);
        $settings->subcategory_name_bg_color_cocktails = $request->input('subcategory_name_bg_color_cocktails', $settings->subcategory_name_bg_color_cocktails);
        $settings->subcategory_name_text_color_cocktails = $request->input('subcategory_name_text_color_cocktails', $settings->subcategory_name_text_color_cocktails);

        $settings->category_name_bg_color_wines = $request->input('category_name_bg_color_wines', $settings->category_name_bg_color_wines);
        $settings->category_name_text_color_wines = $request->input('category_name_text_color_wines', $settings->category_name_text_color_wines);
        $settings->category_name_font_size_wines = $request->input('category_name_font_size_wines', $settings->category_name_font_size_wines);
        $settings->subcategory_name_bg_color_wines = $request->input('subcategory_name_bg_color_wines', $settings->subcategory_name_bg_color_wines);
        $settings->subcategory_name_text_color_wines = $request->input('subcategory_name_text_color_wines', $settings->subcategory_name_text_color_wines);
        $settings->category_name_bg_color_cantina = $request->input('category_name_bg_color_cantina', $settings->category_name_bg_color_cantina);
        $settings->category_name_text_color_cantina = $request->input('category_name_text_color_cantina', $settings->category_name_text_color_cantina);
        $settings->category_name_font_size_cantina = $request->input('category_name_font_size_cantina', $settings->category_name_font_size_cantina);
        $settings->category_name_bg_color_specials = $request->input('category_name_bg_color_specials', $settings->category_name_bg_color_specials);
        $settings->category_name_text_color_specials = $request->input('category_name_text_color_specials', $settings->category_name_text_color_specials);
        $settings->category_name_font_size_specials = $request->input('category_name_font_size_specials', $settings->category_name_font_size_specials);

        $settings->card_bg_color_menu = $request->input('card_bg_color_menu', $settings->card_bg_color_menu);
        $settings->card_bg_color_cocktails = $request->input('card_bg_color_cocktails', $settings->card_bg_color_cocktails);
        $settings->card_bg_color_wines = $request->input('card_bg_color_wines', $settings->card_bg_color_wines);
        $settings->card_bg_color_cantina = $request->input('card_bg_color_cantina', $settings->card_bg_color_cantina);
        $settings->card_bg_color_specials = $request->input('card_bg_color_specials', $settings->card_bg_color_specials);

        $settings->facebook_url = $request->input('facebook_url', $settings->facebook_url);
        $settings->twitter_url = $request->input('twitter_url', $settings->twitter_url);
        $settings->instagram_url = $request->input('instagram_url', $settings->instagram_url);
        $settings->phone_number = $request->input('phone_number', $settings->phone_number);
        $settings->business_hours = $request->input('business_hours', $settings->business_hours);
        if (Schema::hasColumn('settings', 'online_enabled')) {
            $settings->online_enabled = $request->boolean('online_enabled', (bool) ($settings->online_enabled ?? true));
            $settings->online_pause_message = $request->input('online_pause_message', $settings->online_pause_message);

            $scheduleInput = $request->input('online_schedule');
            if (is_array($scheduleInput)) {
                $normalizedSchedule = [];
                foreach ($scheduleInput as $dayKey => $dayConfig) {
                    if (! is_array($dayConfig)) {
                        continue;
                    }
                    $normalizedSchedule[$dayKey] = [
                        'closed' => (bool) ($dayConfig['closed'] ?? false),
                        'start' => $dayConfig['start'] ?? null,
                        'end' => $dayConfig['end'] ?? null,
                    ];
                }
                $settings->online_schedule = $normalizedSchedule;
            }
        }

        $settings->fixed_bottom_font_size = $request->input('fixed_bottom_font_size', $settings->fixed_bottom_font_size);
        $settings->fixed_bottom_font_color = $request->input('fixed_bottom_font_color', $settings->fixed_bottom_font_color);

        if (Schema::hasColumn('settings', 'tip_presets')) {
            $tipPresets = $this->parseTipPresets($request->input('tip_presets'));
            if (empty($tipPresets)) {
                $tipPresets = $settings->tip_presets ?: [15, 18, 20];
            }
            $settings->tip_presets = $tipPresets;
            $settings->tip_allow_custom = $request->boolean(
                'tip_allow_custom',
                $settings->tip_allow_custom ?? true,
            );
            $settings->tip_allow_skip = $request->boolean(
                'tip_allow_skip',
                $settings->tip_allow_skip ?? false,
            );
        }

        if (Schema::hasColumn('settings', 'mobile_ip_restriction_enabled')) {
            $settings->mobile_ip_restriction_enabled = $request->boolean(
                'mobile_ip_restriction_enabled',
                (bool) ($settings->mobile_ip_restriction_enabled ?? false),
            );
            $settings->mobile_ip_allowlist = $request->input('mobile_ip_allowlist', $settings->mobile_ip_allowlist);
            $settings->mobile_ip_bypass_emails = $request->input('mobile_ip_bypass_emails', $settings->mobile_ip_bypass_emails);
        }

        $settings->button_label_menu = $buttonLabelMenu;
        $settings->button_label_cocktails = $buttonLabelCocktails;
        $settings->button_label_wines = $buttonLabelWines;
        $settings->button_label_cantina = $buttonLabelCantina;
        $settings->button_label_events = $buttonLabelEvents;
        $settings->button_label_specials = $buttonLabelSpecials;
        $settings->button_label_vip = $request->input('button_label_vip', $settings->button_label_vip);
        $settings->button_label_reservations = $request->input('button_label_reservations', $settings->button_label_reservations);
        $settings->button_label_online = $buttonLabelOnline;
        if (Schema::hasColumn('settings', 'tab_label_menu')) {
            $settings->tab_label_menu = $this->resolveTabLabel($request->input('tab_label_menu'), $buttonLabelMenu);
            $settings->tab_label_cocktails = $this->resolveTabLabel($request->input('tab_label_cocktails'), $buttonLabelCocktails);
            $settings->tab_label_wines = $this->resolveTabLabel($request->input('tab_label_wines'), $buttonLabelWines);
            if (Schema::hasColumn('settings', 'tab_label_cantina')) {
                $settings->tab_label_cantina = $this->resolveTabLabel($request->input('tab_label_cantina'), $buttonLabelCantina);
            }
            $settings->tab_label_events = $this->resolveTabLabel($request->input('tab_label_events'), $buttonLabelEvents ?: 'Eventos');
            $settings->tab_label_loyalty = $this->resolveTabLabel($request->input('tab_label_loyalty'), $settings->tab_label_loyalty ?? 'Fidelidad');
            $settings->show_tab_menu = $request->boolean('show_tab_menu', (bool) $settings->show_tab_menu);
            $settings->show_tab_cocktails = $request->boolean('show_tab_cocktails', (bool) $settings->show_tab_cocktails);
            $settings->show_tab_wines = $request->boolean('show_tab_wines', (bool) $settings->show_tab_wines);
            if (Schema::hasColumn('settings', 'show_tab_cantina')) {
                $settings->show_tab_cantina = $request->boolean('show_tab_cantina', (bool) $settings->show_tab_cantina);
            }
            $settings->show_tab_events = $request->boolean('show_tab_events', (bool) $settings->show_tab_events);
            $settings->show_tab_campaigns = $request->boolean('show_tab_campaigns', (bool) $settings->show_tab_campaigns);
            $settings->show_tab_popups = $request->boolean('show_tab_popups', (bool) $settings->show_tab_popups);
            $settings->show_tab_loyalty = $request->boolean('show_tab_loyalty', (bool) $settings->show_tab_loyalty);
        }

        if (Schema::hasColumn('settings', 'show_cta_menu')) {
            $settings->show_cta_menu = $request->boolean('show_cta_menu', (bool) $settings->show_cta_menu);
            $settings->show_cta_cafe = $request->boolean('show_cta_cafe', (bool) $settings->show_cta_cafe);
            $settings->show_cta_cocktails = $request->boolean('show_cta_cocktails', (bool) $settings->show_cta_cocktails);
            if (Schema::hasColumn('settings', 'show_cta_cantina')) {
                $settings->show_cta_cantina = $request->boolean('show_cta_cantina', (bool) $settings->show_cta_cantina);
            }
            $settings->show_cta_events = $request->boolean('show_cta_events', (bool) $settings->show_cta_events);
            $settings->show_cta_reservations = $request->boolean('show_cta_reservations', (bool) $settings->show_cta_reservations);
            if (Schema::hasColumn('settings', 'show_cta_online')) {
                $settings->show_cta_online = $request->boolean('show_cta_online', (bool) $settings->show_cta_online);
            }
            if (Schema::hasColumn('settings', 'show_cta_specials')) {
                $settings->show_cta_specials = $request->boolean('show_cta_specials', (bool) ($settings->show_cta_specials ?? true));
            }
        }
        if (Schema::hasColumn('settings', 'show_cta_vip')) {
            $settings->show_cta_vip = $request->boolean('show_cta_vip', (bool) $settings->show_cta_vip);
        }

        if ($request->has('cover_cta_position')) {
            $ctaOrderKeys = [
                'menu',
                'online',
                'cafe',
                'cocktails',
                'cantina',
                'specials',
                'events',
                'reservations',
                'vip',
            ];
            $inputPositions = $request->input('cover_cta_position');
            $positions = [];
            foreach ($ctaOrderKeys as $key) {
                $value = is_array($inputPositions) ? ($inputPositions[$key] ?? null) : null;
                $positions[$key] = is_numeric($value) ? (int) $value : null;
            }
            $sortedOrder = collect($ctaOrderKeys)
                ->sortBy(function ($key, $index) use ($positions) {
                    $position = $positions[$key];
                    return [$position ?? 9999, $index];
                })
                ->values()
                ->all();
            $settings->cover_cta_order = $sortedOrder;
        }

        if ($request->has('cover_cta_target')) {
            $ctaTargetKeys = [
                'menu',
                'online',
                'cafe',
                'cocktails',
                'cantina',
                'specials',
                'events',
                'reservations',
                'vip',
            ];
            $ctaTargetAllowed = $ctaTargetKeys;
            $inputTargets = $request->input('cover_cta_target');
            $targetMap = [];
            foreach ($ctaTargetKeys as $key) {
                $value = is_array($inputTargets) ? ($inputTargets[$key] ?? null) : null;
                $targetMap[$key] = in_array($value, $ctaTargetAllowed, true) ? $value : $key;
            }
            $settings->cover_cta_targets = $targetMap;
        }

        if (Schema::hasColumn('settings', 'featured_card_bg_color')) {
            $settings->featured_card_bg_color = $request->input('featured_card_bg_color', $settings->featured_card_bg_color);
            $settings->featured_card_text_color = $request->input('featured_card_text_color', $settings->featured_card_text_color);
            $settings->featured_tab_bg_color = $request->input('featured_tab_bg_color', $settings->featured_tab_bg_color);
            $settings->featured_tab_text_color = $request->input('featured_tab_text_color', $settings->featured_tab_text_color);
        }

        if ($request->has('clover_merchant_id')) {
            $settings->clover_merchant_id = $request->input('clover_merchant_id') ?: null;
        }
        if ($request->has('clover_env')) {
            $settings->clover_env = $request->input('clover_env') ?: 'production';
        }
        if ($request->has('clover_device_host')) {
            $settings->clover_device_host = $request->input('clover_device_host') ?: null;
        }
        if ($request->has('clover_device_token')) {
            $settings->clover_device_token = $request->input('clover_device_token') ?: null;
        }
        if ($request->boolean('clover_clear_token')) {
            $settings->clover_access_token = null;
        } elseif ($request->filled('clover_access_token')) {
            $settings->clover_access_token = $request->input('clover_access_token');
        }

        $settings->save();

        return redirect()->route('admin.new-panel', ['section' => 'general'])->with('success', 'Configuraciones actualizadas con éxito.');
    }

    public function updateContactInfo(Request $request)
    {
        $settings = Setting::firstOrCreate([]);

        $data = $request->validate([
            'phone_number' => ['nullable', 'string'],
            'business_hours' => ['nullable', 'string'],
            'cover_location_text' => ['nullable', 'string'],
        ]);

        $settings->fill([
            'phone_number' => $data['phone_number'] ?? $settings->phone_number,
            'business_hours' => $data['business_hours'] ?? $settings->business_hours,
            'cover_location_text' => $data['cover_location_text'] ?? $settings->cover_location_text,
        ])->save();

        return redirect()->route('admin.new-panel', ['section' => 'general'])
            ->with('success', 'Contacto y horarios actualizados.');
    }

    
    // app/Http/Controllers/AdminController.php


    public function indexPopups()
    {
        $popups = Popup::all();
        return view('popups.index', compact('popups'));
    }

    public function createPopup()
    {
        $settings = Setting::first();
        return view('popups.create', compact('settings'));
    }

    // AdminController.php

    public function storePopup(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'image' => 'required|file|mimes:jpg,jpeg,png,webp,gif|max:6144',
            'view' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'active' => 'required|boolean',
            'repeat_days' => 'nullable|array'
        ]);

        $imagePath = $request->file('image')->store('popup_images', 'public');

        Popup::create([
            'title' => $request->title,
            'image' => $imagePath,
            'view' => $request->view,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'active' => $request->active,
            'repeat_days' => $request->repeat_days ? implode(',', $request->repeat_days) : null
        ]);

        return redirect()->route('admin.new-panel', ['section' => 'popups'])
            ->with('success', 'Pop-up creado con éxito.');
    }

    public function editPopup(Popup $popup)
    {
        $settings = Setting::first();
        return view('popups.edit', compact('popup', 'settings'));
    }

    public function updatePopup(Request $request, Popup $popup)
    {
        $request->validate([
            'title' => 'required',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:6144',
            'view' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'active' => 'required|boolean',
            'repeat_days' => 'nullable|array'
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('popup_images', 'public');
            $popup->image = $imagePath;
        }

        $popup->title = $request->title;
        $popup->view = $request->view;
        $popup->start_date = $request->start_date;
        $popup->end_date = $request->end_date;
        $popup->active = $request->active;
        $popup->repeat_days = $request->repeat_days ? implode(',', $request->repeat_days) : null;

        $popup->save();

        return redirect()->route('admin.new-panel', ['section' => 'popups'])
            ->with('success', 'Pop-up actualizado con éxito.');
    }
    public function destroyPopup(Popup $popup)
    {
        $popup->delete();
        return redirect()->route('admin.new-panel', ['section' => 'popups'])
            ->with('success', 'Pop-up eliminado con éxito.');
    }

    public function toggleVisibility(Popup $popup)
    {
        $popup->update(['active' => !$popup->active]);
        return redirect()->route('admin.new-panel', ['section' => 'popups'])
            ->with('success', 'Visibilidad del pop-up actualizada.');
    }

}
