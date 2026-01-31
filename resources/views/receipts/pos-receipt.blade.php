<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo</title>
    <style>
        body { font-family: "Courier New", monospace; font-size: 12px; color: #0f172a; margin: 0; }
        .receipt { max-width: 360px; margin: 0 auto; padding: 16px 18px; }
        .center { text-align: center; }
        .muted { color: #64748b; }
        .divider { border-top: 1px dashed #cbd5f5; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 0; vertical-align: top; }
        th { text-align: left; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; }
        .right { text-align: right; }
        .extra { font-size: 11px; color: #475569; }
        .total { font-weight: 700; }
        .logo { max-width: 120px; margin: 0 auto 6px; display: block; }
    </style>
</head>
<body>
    @php
        $methodLabels = [
            'cash' => 'Cash',
            'card' => 'Tarjeta',
            'ath' => 'ATH Movil',
            'split' => 'Combinado',
            'tap_to_pay' => 'Tap to Pay',
        ];
        $channelLabels = [
            'walkin' => 'Walk-in',
            'phone' => 'Telefono',
        ];
        $methodLabel = $methodLabels[$receipt['payment_method'] ?? ''] ?? ($receipt['payment_method'] ?? 'N/A');
        $channelLabel = $channelLabels[$receipt['service_channel'] ?? ''] ?? ($receipt['service_channel'] ?? 'N/A');
        $subtotal = $receipt['subtotal'] ?? ($receipt['total'] ?? 0);
        $taxTotal = $receipt['tax_total'] ?? 0;
        $taxes = $receipt['taxes'] ?? [];
        $tipTotal = $receipt['tip_total'] ?? 0;
        $grandTotal = $receipt['total'] ?? ($subtotal + $taxTotal + $tipTotal);
        $restaurantName = $receipt['restaurant_name'] ?? config('app.name');
        $logo = $receipt['restaurant_logo'] ?? null;
        $stage = $receipt['receipt_stage'] ?? ($receipt['is_paid'] ?? false ? 'paid' : 'pre');
        $title = $stage === 'pre' ? 'Cuenta abierta' : 'Recibo pagado';
        $issuedAt = $receipt['paid_at'] ?? $receipt['issued_at'] ?? $receipt['created_at'] ?? '';
        $totalLabel = $stage === 'pre' ? 'Total pendiente' : 'Total pagado';
    @endphp

    <div class="receipt">
        <div class="center">
            @if($logo)
                <img src="{{ $logo }}" alt="Logo" class="logo">
            @endif
            <div class="total">{{ $restaurantName }}</div>
            <div class="muted">{{ $title }}</div>
            <div class="muted">Ticket #{{ $receipt['order_id'] ?? '—' }}</div>
        </div>

        <div class="divider"></div>

        <div>
            <div>Mesa: {{ $receipt['table_label'] ?? '—' }} · {{ $channelLabel }}</div>
            <div>Cliente: {{ $receipt['guest_name'] ?? 'N/A' }}</div>
            <div>Mesero: {{ $receipt['server_name'] ?? 'N/A' }}</div>
            <div>Fecha: {{ $issuedAt }}</div>
            <div>Metodo: {{ $methodLabel }}</div>
            @if(!empty($receipt['payment_provider']))
                <div>Procesador: {{ $receipt['payment_provider'] }}</div>
            @endif
        </div>

        <div class="divider"></div>

        @if(!empty($receipt['payment_details']))
            <table>
                <thead>
                    <tr>
                        <th>Detalle pago</th>
                        <th class="right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receipt['payment_details'] as $detail)
                        <tr>
                            <td>{{ $detail['label'] ?? '' }}</td>
                            <td class="right">{{ $detail['value'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="divider"></div>
        @endif

        <table>
            <thead>
                <tr>
                    <th>Cant Item</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($receipt['items'] ?? []) as $item)
                    <tr>
                        <td>
                            {{ $item['quantity'] ?? 1 }}x {{ $item['name'] ?? '' }}
                            @if(!empty($item['extras']))
                                @foreach($item['extras'] as $extra)
                                    <div class="extra">
                                        + {{ $extra['name'] ?? '' }} @if(($extra['quantity'] ?? 1) > 1) x{{ $extra['quantity'] }}@endif
                                    </div>
                                @endforeach
                            @endif
                            @if(!empty($item['notes']))
                                <div class="extra">Nota: {{ $item['notes'] }}</div>
                            @endif
                        </td>
                        <td class="right">${{ number_format($item['line_total'] ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <table>
            <tr>
                <td class="total">Subtotal</td>
                <td class="right total">${{ number_format($subtotal, 2) }}</td>
            </tr>
            @if(!empty($taxes))
                @foreach($taxes as $tax)
                    <tr>
                        <td class="total">
                            {{ $tax['name'] ?? 'Impuesto' }}
                            @if(isset($tax['rate']))
                                <span class="muted">({{ number_format((float) $tax['rate'], 2) }}%)</span>
                            @endif
                        </td>
                        <td class="right total">${{ number_format($tax['amount'] ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            @elseif($taxTotal > 0)
                <tr>
                    <td class="total">Impuestos</td>
                    <td class="right total">${{ number_format($taxTotal, 2) }}</td>
                </tr>
            @endif
            @if($tipTotal > 0)
                <tr>
                    <td class="total">Propina</td>
                    <td class="right total">${{ number_format($tipTotal, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="total">{{ $totalLabel }}</td>
                <td class="right total">${{ number_format($grandTotal, 2) }}</td>
            </tr>
        </table>

        <div class="divider"></div>
        <div class="center muted">Gracias por tu visita</div>
    </div>
</body>
</html>
