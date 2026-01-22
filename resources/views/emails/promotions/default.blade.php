@php
    $hero = $promotion->hero_image
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($promotion->hero_image)
        : null;
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $promotion->subject }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #111827;
        }
        .container {
            max-width: 640px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        }
        .hero img {
            width: 100%;
            display: block;
        }
        .content {
            padding: 32px;
            line-height: 1.6;
        }
        .cta {
            display: inline-block;
            margin-top: 24px;
            padding: 12px 28px;
            background: linear-gradient(120deg,#fbbf24,#f97316);
            color: #111827;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            padding: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        @if($hero)
            <div class="hero">
                <img src="{{ $hero }}" alt="Promoción">
            </div>
        @endif
        <div class="content">
            {!! $promotion->body_html !!}
        </div>
    </div>
    <div class="footer">
        © {{ date('Y') }} {{ config('app.name', 'Café Negro') }} · Este correo fue enviado desde nuestro panel de experiencias.
    </div>
</body>
</html>
