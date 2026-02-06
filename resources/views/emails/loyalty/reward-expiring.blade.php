@php
    $redeemUrl = route('loyalty.redeem.show', $redemption->qr_token);
    $qrImage = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' . urlencode($redeemUrl);
    $expiresAt = $redemption->expires_at?->format('d M Y');
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recompensa por expirar</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 24px;
            color: #1f2937;
        }
        .card {
            max-width: 640px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        }
        h1 {
            font-size: 26px;
            margin-bottom: 16px;
        }
        p {
            line-height: 1.6;
        }
        .reward {
            margin: 24px 0;
            padding: 18px;
            border-radius: 16px;
            background: linear-gradient(120deg,#fde68a,#f97316);
            color: #111827;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Hola {{ $customer->name }},</h1>
        <p>Tu recompensa está por expirar en <strong>{{ $daysRemaining }} días</strong>.</p>

        <div class="reward">
            <h2 style="margin:0 0 8px;">{{ $reward->title }}</h2>
            @if($reward->description)
                <p style="margin:0;">{{ $reward->description }}</p>
            @endif
            <p style="margin:12px 0 0;"><strong>Puntos requeridos:</strong> {{ $reward->points_required }}</p>
            @if($expiresAt)
                <p style="margin:6px 0 0;"><strong>Expira:</strong> {{ $expiresAt }}</p>
            @endif
        </div>

        <div style="text-align:center; margin: 20px 0;">
            <p style="margin:0 0 12px;">Muestra este QR al mesero para validar tu recompensa:</p>
            <img src="{{ $qrImage }}" alt="QR de recompensa" width="240" height="240" style="display:inline-block; border-radius:12px; border:1px solid #e5e7eb;">
            <p style="font-size:12px; color:#6b7280; margin-top:10px;">
                Si no puedes escanear, usa este enlace: <a href="{{ $redeemUrl }}">{{ $redeemUrl }}</a>
            </p>
        </div>

        <p class="footer">
            © {{ date('Y') }} {{ config('app.name', 'Madeleine Cafe Bistro') }} &middot; Gracias por visitarnos.
        </p>
    </div>
</body>
</html>
