<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo</title>
</head>
<body style="font-family: 'Courier New', monospace; background:#f1f5f9; padding: 20px;">
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

    <div style="max-width: 420px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 20px; border: 1px dashed #e2e8f0;">
        <div style="text-align: center;">
            @if($logo)
                <img src="{{ $logo }}" alt="Logo" style="max-width: 120px; margin: 0 auto 8px; display: block;">
            @endif
            <div style="font-weight: 700; font-size: 14px;">{{ $restaurantName }}</div>
            <div style="color: #64748b; margin-top: 2px;">{{ $title }}</div>
            <div style="color: #64748b;">Ticket #{{ $receipt['order_id'] ?? '—' }}</div>
        </div>

        <div style="border-top: 1px dashed #cbd5f5; margin: 12px 0;"></div>

        <div style="color: #0f172a; font-size: 12px;">
            <div>Mesa: {{ $receipt['table_label'] ?? '—' }} · {{ $channelLabel }}</div>
            <div>Cliente: {{ $receipt['guest_name'] ?? 'N/A' }}</div>
            <div>Mesero: {{ $receipt['server_name'] ?? 'N/A' }}</div>
            <div>Fecha: {{ $issuedAt }}</div>
            <div>Metodo: {{ $methodLabel }}</div>
            @if(!empty($receipt['payment_provider']))
                <div>Procesador: {{ $receipt['payment_provider'] }}</div>
            @endif
        </div>

        <div style="border-top: 1px dashed #cbd5f5; margin: 12px 0;"></div>

        @if(!empty($receipt['payment_details']))
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
                <thead>
                    <tr>
                        <th style="text-align: left; padding-bottom: 6px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em;">Detalle pago</th>
                        <th style="text-align: right; padding-bottom: 6px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receipt['payment_details'] as $detail)
                        <tr>
                            <td style="padding: 4px 0;">{{ $detail['label'] ?? '' }}</td>
                            <td style="text-align: right; padding: 4px 0;">{{ $detail['value'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left; padding-bottom: 6px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em;">Cant Item</th>
                    <th style="text-align: right; padding-bottom: 6px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($receipt['items'] ?? []) as $item)
                    <tr>
                        <td style="padding: 6px 0;">
                            {{ $item['quantity'] ?? 1 }}x {{ $item['name'] ?? '' }}
                            @if(!empty($item['extras']))
                                <div style="font-size: 11px; color: #475569; margin-top: 4px;">
                                    @foreach($item['extras'] as $extra)
                                        <div>+ {{ $extra['name'] ?? '' }} @if(($extra['quantity'] ?? 1) > 1) x{{ $extra['quantity'] }}@endif</div>
                                    @endforeach
                                </div>
                            @endif
                            @if(!empty($item['notes']))
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">
                                    Nota: {{ $item['notes'] }}
                                </div>
                            @endif
                        </td>
                        <td style="padding: 6px 0; text-align: right;">
                            ${{ number_format($item['line_total'] ?? 0, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="border-top: 1px dashed #cbd5f5; margin: 12px 0;"></div>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="font-weight: 700;">Subtotal</td>
                <td style="text-align: right; font-weight: 700;">${{ number_format($subtotal, 2) }}</td>
            </tr>
            @if(!empty($taxes))
                @foreach($taxes as $tax)
                    <tr>
                        <td style="font-weight: 700;">{{ $tax['name'] ?? 'Impuesto' }}</td>
                        <td style="text-align: right; font-weight: 700;">${{ number_format($tax['amount'] ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            @elseif($taxTotal > 0)
                <tr>
                    <td style="font-weight: 700;">Impuestos</td>
                    <td style="text-align: right; font-weight: 700;">${{ number_format($taxTotal, 2) }}</td>
                </tr>
            @endif
            @if($tipTotal > 0)
                <tr>
                    <td style="font-weight: 700;">Propina</td>
                    <td style="text-align: right; font-weight: 700;">${{ number_format($tipTotal, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td style="font-weight: 700;">{{ $totalLabel }}</td>
                <td style="text-align: right; font-weight: 700;">${{ number_format($grandTotal, 2) }}</td>
            </tr>
        </table>

        <div style="border-top: 1px dashed #cbd5f5; margin: 12px 0;"></div>

        <div style="text-align: center; color: #64748b;">Gracias por tu visita</div>
        <div style="text-align: center; margin-top: 10px;">
            <a href="{{ $downloadUrl }}" style="color: #f59e0b; font-weight: 700;">Descargar recibo PDF</a>
        </div>
    </div>
</body>
</html>
