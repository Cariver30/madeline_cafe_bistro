<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderBatch;
use App\Support\Printing\PrintJobBuilder;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class ServerOrderController extends Controller
{
    public function confirm(Request $request, OrderBatch $order)
    {
        $server = $request->user();
        $isManager = $server?->isManager() ?? false;

        $order->loadMissing(['order']);
        abort_unless($isManager || $order->order?->server_id === $server->id, Response::HTTP_FORBIDDEN);

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'La orden ya fue procesada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        app(PrintJobBuilder::class)->createForBatch($order);

        return response()->json([
            'message' => 'Orden confirmada.',
            'order' => $order->fresh(),
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
