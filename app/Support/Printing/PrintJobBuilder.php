<?php

namespace App\Support\Printing;

use App\Models\Order;
use App\Models\OrderBatch;
use App\Models\PrinterRoute;
use App\Models\PrintJob;
use Illuminate\Support\Collection;

class PrintJobBuilder
{
    public function createForOrder(Order $order): int
    {
        $order->loadMissing(['items.extras', 'tableSession', 'server']);

        $routes = PrinterRoute::with(['printer', 'template'])
            ->where('enabled', true)
            ->whereHas('printer', fn ($query) => $query->where('is_active', true))
            ->whereHas('template', fn ($query) => $query->where('is_active', true))
            ->get();

        if ($routes->isEmpty()) {
            return 0;
        }

        $renderer = new PrintTemplateRenderer();
        $created = 0;

        foreach ($routes as $route) {
            $items = $this->filterItems($order->items, $route->category_scope, $route->category_id);
            if ($items->isEmpty()) {
                continue;
            }

            $template = $route->template;
            if (!$template || !$route->printer) {
                continue;
            }

            if ($template->type === 'label') {
                foreach ($items as $item) {
                    $payload = $renderer->render($template, $order, $item);
                    $this->storeJob($route->printer->id, $order->id, $template->id, $payload);
                    $created++;
                }
                continue;
            }

            $originalItems = $order->items;
            $order->setRelation('items', $items);
            $payload = $renderer->render($template, $order);
            $order->setRelation('items', $originalItems);
            $this->storeJob($route->printer->id, $order->id, $template->id, $payload);
            $created++;
        }

        return $created;
    }

    public function createForBatch(OrderBatch $batch): int
    {
        $batch->loadMissing(['items.extras', 'order.tableSession', 'order.server']);
        $order = $batch->order;
        if (!$order) {
            return 0;
        }

        $routes = PrinterRoute::with(['printer', 'template'])
            ->where('enabled', true)
            ->whereHas('printer', fn ($query) => $query->where('is_active', true))
            ->whereHas('template', fn ($query) => $query->where('is_active', true))
            ->get();

        if ($routes->isEmpty()) {
            return 0;
        }

        $renderer = new PrintTemplateRenderer();
        $created = 0;
        $items = $batch->items;

        foreach ($routes as $route) {
            $filteredItems = $this->filterItems($items, $route->category_scope, $route->category_id);
            if ($filteredItems->isEmpty()) {
                continue;
            }

            $template = $route->template;
            if (!$template || !$route->printer) {
                continue;
            }

            if ($template->type === 'label') {
                foreach ($filteredItems as $item) {
                    $payload = $renderer->render($template, $order, $item);
                    $this->storeJob($route->printer->id, $order->id, $template->id, $payload);
                    $created++;
                }
                continue;
            }

            $originalItems = $order->items;
            $order->setRelation('items', $filteredItems);
            $payload = $renderer->render($template, $order);
            $order->setRelation('items', $originalItems);
            $this->storeJob($route->printer->id, $order->id, $template->id, $payload);
            $created++;
        }

        return $created;
    }

    private function filterItems(Collection $items, string $scope, ?int $categoryId): Collection
    {
        if ($scope === 'all') {
            return $items;
        }

        return $items->filter(function ($item) use ($scope, $categoryId) {
            if ($item->category_scope !== $scope) {
                return false;
            }
            if ($categoryId === null) {
                return true;
            }
            return (int) $item->category_id === (int) $categoryId;
        })->values();
    }

    private function storeJob(int $printerId, int $orderId, int $templateId, string $payload): void
    {
        PrintJob::create([
            'printer_id' => $printerId,
            'order_id' => $orderId,
            'print_template_id' => $templateId,
            'payload' => $payload,
            'content_type' => 'text/plain',
            'status' => 'pending',
        ]);
    }
}
