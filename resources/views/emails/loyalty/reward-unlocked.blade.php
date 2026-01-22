@php
    $copy = trim(optional($settings)->loyalty_email_copy ?? '');
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recompensa desbloqueada</title>
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
            margin: 28px 0;
            padding: 20px;
            border-radius: 16px;
            background: linear-gradient(120deg,#fde68a,#f97316);
            color: #111827;
        }
        .cta {
            display: inline-block;
            margin-top: 24px;
            padding: 12px 28px;
            border-radius: 999px;
            background: #111827;
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
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
        <p>Acabas de acumular los puntos necesarios para desbloquear una nueva recompensa dentro del programa de fidelidad de {{ config('app.name', 'Café Negro') }}.</p>

        <div class="reward">
            <h2 style="margin:0 0 8px;">{{ $reward->title }}</h2>
            @if($reward->description)
                <p style="margin:0;">{{ $reward->description }}</p>
            @endif
            <p style="margin:12px 0 0;"><strong>Puntos requeridos:</strong> {{ $reward->points_required }}</p>
        </div>

        @if($copy)
            <p>{!! nl2br(e($copy)) !!}</p>
        @else
            <p>Preséntate en nuestra barra y muestra este correo para coordinar la redención. Nuestro equipo confirmará la disponibilidad y el mejor momento para disfrutar tu recompensa.</p>
        @endif

        <p class="footer">
            © {{ date('Y') }} {{ config('app.name', 'Café Negro') }} &middot; Gracias por ser parte de nuestra comunidad.
        </p>
    </div>
</body>
</html>
