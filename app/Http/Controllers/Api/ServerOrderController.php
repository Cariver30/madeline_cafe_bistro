<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderBatch;
use App\Models\Setting;
use App\Support\CloverBillingClient;
use App\Support\CloverOrderService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Throwable;

class ServerOrderController extends Controller
{
    public function confirm(Request $request, OrderBatch $order)
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $server = $request->user();
        $isManager = $server?->isManager() ?? false;

        $order->loadMissing(['order', 'items.itemable', 'items.extras.extra']);
        abort_unless($isManager || $order->order?->server_id === $server->id, Response::HTTP_FORBIDDEN);

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'La orden ya fue procesada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $settings = Setting::first();
        $cloverService = CloverOrderService::fromSettings($settings);
        if (! $cloverService) {
            return response()->json([
                'message' => 'Configura las credenciales de Clover antes de imprimir.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $cloverResult = $cloverService->sendBatch($order, $server);
        } catch (Throwable $exception) {
            report($exception);
            return response()->json([
                'message' => 'No se pudo enviar la orden a Clover.',
                'error' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $meteredResult = null;
        $billingClient = CloverBillingClient::fromSettings($settings);
        $eventId = config('services.clover.metered_event_id');
        if ($billingClient && $eventId && $settings?->clover_merchant_id) {
            try {
                $meteredResult = $billingClient->reportEvent(
                    $eventId,
                    $settings->clover_merchant_id,
                    1
                );
            } catch (Throwable $exception) {
                report($exception);
                $meteredResult = ['error' => $exception->getMessage()];
            }
        }

        $order->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'clover_order_id' => $cloverResult['order_id'] ?? null,
            'clover_print_event_id' => $cloverResult['print_event_id'] ?? null,
            'metered_opened_at' => $meteredResult && empty($meteredResult['error']) ? now() : null,
        ]);

        return response()->json([
            'message' => 'Orden confirmada.',
            'order' => $order->fresh(),
            'clover' => $cloverResult,
            'metered_event' => $meteredResult,
        ]);
    }

    public function cancel(Request $request, OrderBatch $order)
    {
        $server = $request->user();
        $isManager = $server?->isManager() ?? false;

        $order->loadMissing(['order']);
        abort_unless($isManager || $order->order?->server_id === $server->id, Response::HTTP_FORBIDDEN);

        if ($order->status === 'cancelled') {
            return response()->json([
                'message' => 'La orden ya fue cancelada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($order->status !== 'pending' && ! $isManager) {
            return response()->json([
                'message' => 'La orden ya fue procesada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Orden cancelada.',
            'order' => $order->fresh(),
        ]);
    }
}
