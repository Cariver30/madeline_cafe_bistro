<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class OrderItemController extends Controller
{
    public function void(Request $request, OrderBatch $order, OrderItem $item)
    {
        $user = $request->user();
        $isManager = $user?->isManager() ?? false;

        $order->loadMissing('order');
        abort_unless($isManager || $order->order?->server_id === $user->id, Response::HTTP_FORBIDDEN);
        abort_unless($item->order_batch_id === $order->id, Response::HTTP_NOT_FOUND);

        if ($item->voided_at) {
            return response()->json([
                'message' => 'El item ya fue anulado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $isManager && $order->status !== 'pending') {
            return response()->json([
                'message' => 'Requiere autorización de gerente.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($order->status === 'cancelled') {
            return response()->json([
                'message' => 'La orden ya fue cancelada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validator = Validator::make($request->all(), [
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa el motivo.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item->update([
            'voided_at' => now(),
            'voided_by' => $user->id,
            'void_reason' => $validator->validated()['reason'] ?? null,
        ]);

        return response()->json([
            'message' => 'Item anulado.',
        ]);
    }

    public function override(Request $request, OrderBatch $order, OrderItem $item)
    {
        $user = $request->user();
        $order->loadMissing('order');

        abort_unless(
            $order->order?->server_id === $user->id || ($user?->isManager() ?? false),
            Response::HTTP_FORBIDDEN
        );
        abort_unless($item->order_batch_id === $order->id, Response::HTTP_NOT_FOUND);

        if ($item->voided_at) {
            return response()->json([
                'message' => 'El item ya fue anulado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validator = Validator::make($request->all(), [
            'manager_email' => ['required', 'email', 'max:255'],
            'manager_password' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Credenciales inválidas.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $validator->validated();
        $manager = User::where('email', strtolower(trim($data['manager_email'])))
            ->where('role', 'manager')
            ->where('active', true)
            ->first();

        if (! $manager || ! Hash::check($data['manager_password'], $manager->password)) {
            return response()->json([
                'message' => 'Autorización de gerente inválida.',
            ], Response::HTTP_FORBIDDEN);
        }

        $item->update([
            'voided_at' => now(),
            'voided_by' => $manager->id,
            'void_reason' => $data['reason'] ?? null,
        ]);

        return response()->json([
            'message' => 'Item anulado con autorización.',
        ]);
    }
}
