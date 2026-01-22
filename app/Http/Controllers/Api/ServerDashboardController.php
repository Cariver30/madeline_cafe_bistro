<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TableSession;
use App\Support\Orders\PosReceiptBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ServerDashboardController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();
        $isManager = $user?->isManager() ?? false;
        $serverId = $user->id;

        if ($isManager && $request->filled('server_id')) {
            $serverId = (int) $request->input('server_id');
        }

        $start = Carbon::today();
        $end = Carbon::today()->endOfDay();

        $orders = Order::with(['items.extras'])
            ->where('server_id', $serverId)
            ->where('status', 'confirmed')
            ->where(function ($query) use ($start, $end) {
                $query
                    ->whereBetween('paid_at', [$start, $end])
                    ->orWhereBetween('confirmed_at', [$start, $end]);
            })
            ->get();

        $salesTotal = 0.0;
        $tipsTotal = 0.0;

        foreach ($orders as $order) {
            $tipsTotal += (float) ($order->tip_total ?? 0);
            if ($order->paid_total !== null) {
                $salesTotal += (float) $order->paid_total;
                continue;
            }
            $salesTotal += PosReceiptBuilder::calculateTotal($order);
        }

        $tablesClosed = TableSession::where('server_id', $serverId)
            ->whereBetween('closed_at', [$start, $end])
            ->count();

        $activeTables = TableSession::where('server_id', $serverId)
            ->whereIn('status', ['active', 'expired'])
            ->count();

        return response()->json([
            'summary' => [
                'sales_total' => round($salesTotal, 2),
                'tips_total' => round($tipsTotal, 2),
                'orders_count' => $orders->count(),
                'tables_closed' => $tablesClosed,
                'active_tables' => $activeTables,
            ],
        ]);
    }
}
