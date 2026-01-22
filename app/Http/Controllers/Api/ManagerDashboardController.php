<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyVisit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TableSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ManagerDashboardController extends Controller
{
    public function summary()
    {
        $today = Carbon::today();
        $weekAgo = $today->copy()->subDays(6);

        $totals = [
            'total_visits' => LoyaltyVisit::count(),
            'pending_visits' => LoyaltyVisit::where('status', 'pending')->count(),
            'confirmed_visits' => LoyaltyVisit::where('status', 'confirmed')->count(),
            'points_distributed' => LoyaltyVisit::sum('points_awarded'),
        ];

        $daily = LoyaltyVisit::selectRaw('DATE(created_at) as day, COUNT(*) as visits')
            ->whereBetween('created_at', [$weekAgo->toDateString(), $today->toDateString()])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($row) => [
                'day' => $row->day,
                'visits' => (int) $row->visits,
            ]);

        return response()->json([
            'totals' => $totals,
            'daily_visits' => $daily,
        ]);
    }

    public function servers()
    {
        $servers = User::where('role', 'server')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'active' => (bool) $user->active,
                'last_login' => $user->updated_at?->toIso8601String(),
            ]);

        return response()->json([
            'servers' => $servers,
        ]);
    }

    public function operations()
    {
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

        $salesTotal = (float) $ordersToday->sum(DB::raw('COALESCE(paid_total, 0)'));
        $tipsTotal = (float) $ordersToday->sum(DB::raw('COALESCE(tip_total, 0)'));
        $ordersCount = (int) $ordersToday->count();

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

        $salesByChannel = Order::selectRaw('table_sessions.service_channel as channel, SUM(COALESCE(orders.paid_total, 0)) as sales_total, COUNT(*) as orders_count')
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

        $topItems = OrderItem::selectRaw('name, SUM(quantity) as quantity, SUM(quantity * unit_price) as revenue')
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

        $servers = User::where('role', 'server')
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

        return response()->json([
            'totals' => [
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
            ],
            'sales_by_channel' => $salesByChannel,
            'top_items' => $topItems,
            'servers' => $servers,
        ]);
    }

    public function toggleServer(Request $request, User $user)
    {
        if (!$user->isServer()) {
            return response()->json([
                'message' => 'Solo puedes administrar meseros.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->active = !$user->active;
        $user->save();

        return response()->json([
            'message' => $user->active ? 'Mesero activado.' : 'Mesero desactivado.',
            'server' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'active' => (bool) $user->active,
            ],
        ]);
    }
}
