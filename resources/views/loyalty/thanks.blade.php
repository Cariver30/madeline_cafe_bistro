<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-white flex items-center justify-center px-4">
    <div class="max-w-md text-center space-y-4">
        <p class="text-xs uppercase tracking-[0.35em] text-amber-300">Visita confirmada</p>
        <h1 class="text-3xl font-semibold">¡Listo!</h1>
        <p class="text-white/70 text-sm">Sumamos tus puntos. Nuestro equipo revisará cuando alcances una recompensa y te escribiremos a tu correo.</p>
        <a href="{{ route('cover') }}" class="inline-flex items-center justify-center rounded-full bg-amber-400 text-slate-950 px-6 py-3 font-semibold">Volver a inicio</a>
    </div>
</body>
</html>
