<?php

namespace App\Http\Controllers\Api;

use App\Events\KitchenItemStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\PrepLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class KitchenOrderController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label_id' => ['nullable', 'exists:prep_labels,id'],
            'area_id' => ['nullable', 'exists:prep_areas,id'],
            'status' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Parámetros inválidos.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $labelId = $request->integer('label_id');
        $areaId = $request->integer('area_id');
        $statusFilter = $request->string('status')->toString();

        if (!$labelId && !$areaId) {
            return response()->json([
                'message' => 'Selecciona un área o un label.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $statusList = $this->resolveStatusFilter($statusFilter);

        $items = OrderItem::with([
            'order.tableSession',
            'order.server',
            'extras',
            'prepLabels.area',
        ])
            ->whereNull('voided_at')
            ->whereHas('order', function ($query) {
                $query->where('status', '!=', 'cancelled');
            })
            ->whereHas('prepLabels', function ($query) use ($labelId, $areaId, $statusList) {
                if ($labelId) {
                    $query->where('prep_labels.id', $labelId);
                }
                if ($areaId) {
                    $query->where('prep_labels.prep_area_id', $areaId);
                }
                if (!empty($statusList)) {
                    $query->whereIn('order_item_prep_labels.status', $statusList);
                }
            })
            ->orderByDesc('created_at')
            ->get();

        $orders = $items->groupBy('order_id')->map(function ($itemsGroup) use ($labelId, $areaId) {
            $first = $itemsGroup->first();
            $order = $first?->order;
            $session = $order?->tableSession;

            return [
                'order_id' => $order?->id,
                'table_label' => $session?->table_label,
                'guest_name' => $session?->guest_name,
                'party_size' => $session?->party_size,
                'server_name' => $order?->server?->name,
                'created_at' => optional($order?->created_at)->toIso8601String(),
                'items' => $itemsGroup->map(function (OrderItem $item) use ($labelId, $areaId) {
                    $matchingLabels = $item->prepLabels->filter(function (PrepLabel $label) use ($labelId, $areaId) {
                        if ($labelId && $label->id !== $labelId) {
                            return false;
                        }
                        if ($areaId && $label->prep_area_id !== $areaId) {
                            return false;
                        }
                        return true;
                    })->values();

                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'quantity' => $item->quantity,
                        'notes' => $item->notes,
                        'labels' => $matchingLabels->map(function (PrepLabel $label) {
                            return [
                                'id' => $label->id,
                                'name' => $label->name,
                                'area_id' => $label->prep_area_id,
                                'area_name' => $label->area?->name,
                                'status' => $label->pivot?->status ?? 'pending',
                            ];
                        }),
                        'extras' => $item->extras->map(fn ($extra) => [
                            'id' => $extra->id,
                            'name' => $extra->name,
                            'price' => $extra->price,
                            'quantity' => $extra->quantity,
                        ]),
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function update(Request $request, OrderItem $orderItem)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'label_id' => ['required', 'exists:prep_labels,id'],
            'status' => ['required', 'in:pending,preparing,ready,delivered,cancelled'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa los datos enviados.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $validator->validated();
        $labelId = (int) $data['label_id'];
        $status = $data['status'];

        $label = $orderItem->prepLabels()->where('prep_labels.id', $labelId)->first();
        if (!$label) {
            return response()->json([
                'message' => 'Label no asignado al item.',
            ], Response::HTTP_NOT_FOUND);
        }

        $orderItem->loadMissing('order');

        if ($user && $user->role === 'server') {
            if ($orderItem->order?->server_id !== $user->id) {
                return response()->json([
                    'message' => 'No tienes permisos para esta orden.',
                ], Response::HTTP_FORBIDDEN);
            }
            if ($status !== 'delivered') {
                return response()->json([
                    'message' => 'Solo puedes marcar como entregado.',
                ], Response::HTTP_FORBIDDEN);
            }
            if (($label->pivot?->status ?? 'pending') !== 'ready') {
                return response()->json([
                    'message' => 'Solo puedes entregar items listos.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $updates = [
            'status' => $status,
            'updated_by' => $request->user()?->id,
        ];

        if ($status === 'preparing') {
            $updates['prepared_at'] = now();
        }
        if ($status === 'ready') {
            $updates['ready_at'] = now();
        }
        if ($status === 'delivered') {
            $updates['delivered_at'] = now();
        }

        $orderItem->prepLabels()->updateExistingPivot($labelId, $updates);

        event(new KitchenItemStatusUpdated(
            $orderItem->order_id,
            $orderItem->id,
            $labelId,
            $label->prep_area_id,
            $status,
            $orderItem->order?->server_id,
        ));

        return response()->json([
            'message' => 'Estado actualizado.',
        ]);
    }

    private function resolveStatusFilter(string $statusFilter): array
    {
        if ($statusFilter === '') {
            return ['pending', 'preparing', 'ready'];
        }

        $values = collect(explode(',', $statusFilter))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $allowed = ['pending', 'preparing', 'ready', 'delivered', 'cancelled'];

        return array_values(array_intersect($allowed, $values));
    }
}
