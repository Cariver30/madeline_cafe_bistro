@php
    $storeName = config('app.name', 'Café Negro');
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Puntos actualizados</title>
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
            margin-bottom: 12px;
        }
        p {
            line-height: 1.6;
        }
        .points {
            margin: 24px 0;
            padding: 18px;
            border-radius: 16px;
            background: linear-gradient(120deg,#fde68a,#f97316);
            color: #111827;
            text-align: center;
        }
        .points strong {
            font-size: 32px;
            display: block;
            margin-top: 6px;
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
        <p>Gracias por tu visita en {{ $storeName }}. Se han añadido {{ $pointsAwarded }} puntos a tu cuenta.</p>

        <div class="points">
            Puntos acumulados
            <strong>{{ $currentPoints }}</strong>
        </div>

        <p>Te avisaremos cuando desbloquees una nueva recompensa.</p>

        <p class="footer">
            © {{ date('Y') }} {{ $storeName }} &middot; Gracias por ser parte de nuestra comunidad.
        </p>
    </div>
</body>
</html>
