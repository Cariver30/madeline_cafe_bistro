<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'Mesa no disponible' }}</title>
    <style>
        body {
            margin: 0;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: radial-gradient(circle at top, #0f172a, #020617);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
        }
        .card {
            max-width: 520px;
            width: 100%;
            background: rgba(15, 23, 42, 0.85);
            border-radius: 24px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            padding: 28px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
        }
        h1 {
            margin: 0 0 12px;
            font-size: 28px;
            color: #fbbf24;
        }
        p {
            margin: 0;
            font-size: 16px;
            color: #cbd5f5;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ $title ?? 'Mesa no disponible' }}</h1>
        <p>{{ $message ?? 'Solicita ayuda a tu mesero.' }}</p>
    </div>
</body>
</html>
