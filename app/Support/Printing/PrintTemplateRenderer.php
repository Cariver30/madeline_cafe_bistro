<?php

namespace App\Support\Printing;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PrintTemplate;

class PrintTemplateRenderer
{
    public function render(PrintTemplate $template, Order $order, ?OrderItem $item = null): string
    {
        $tableSession = $order->tableSession;
        $itemsText = $this->formatItems($order->items);
        $itemExtras = $item ? $this->formatExtras($item) : '';

        $replacements = [
            '{{order_id}}' => (string) $order->id,
            '{{status}}' => $order->status,
            '{{created_at}}' => optional($order->created_at)->format('Y-m-d H:i'),
            '{{table}}' => $tableSession?->table_label ?? '',
            '{{guest}}' => $tableSession?->guest_name ?? '',
            '{{guest_email}}' => $tableSession?->guest_email ?? '',
            '{{guest_phone}}' => $tableSession?->guest_phone ?? '',
            '{{party_size}}' => $tableSession?->party_size ? (string) $tableSession->party_size : '',
            '{{server}}' => $order->server?->name ?? '',
            '{{items}}' => $itemsText,
            '{{item_name}}' => $item?->name ?? '',
            '{{item_qty}}' => $item ? (string) $item->quantity : '',
            '{{item_notes}}' => $item?->notes ?? '',
            '{{item_extras}}' => $itemExtras,
            '{{category}}' => $item?->category_name ?? '',
            '{{scope}}' => $item?->category_scope ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template->body);
    }

    private function formatItems($items): string
    {
        $lines = [];
        foreach ($items as $item) {
            $line = "{$item->quantity}x {$item->name}";
            if (!empty($item->notes)) {
                $line .= " ({$item->notes})";
            }
            $extras = $this->formatExtras($item);
            if ($extras) {
                $line .= " + {$extras}";
            }
            $lines[] = $line;
        }

        return implode(PHP_EOL, $lines);
    }

    private function formatExtras(OrderItem $item): string
    {
        if (!$item->relationLoaded('extras')) {
            $item->load('extras');
        }

        if ($item->extras->isEmpty()) {
            return '';
        }

        return $item->extras
            ->map(fn ($extra) => "{$extra->quantity}x {$extra->name}")
            ->implode(', ');
    }
}
