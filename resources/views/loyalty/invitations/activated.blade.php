<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Acceso activado</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-white flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white/10 border border-white/20 rounded-3xl p-8 backdrop-blur">
        <p class="text-xs uppercase tracking-[0.3em] text-amber-300 mb-4">Acceso activado</p>
        <h1 class="text-2xl font-semibold mb-2">Ya puedes entrar a la app</h1>
        <p class="text-white/70 text-sm mb-6">
            Tu cuenta de {{ $roleLabel ?? 'usuario' }} está lista. Abre la app y entra con tu correo y la contraseña que acabas de crear.
        </p>
        <div class="rounded-2xl bg-white/5 border border-white/10 p-4 text-sm text-white/80">
            <p class="font-semibold text-white mb-1">Datos de acceso</p>
            <p>{{ $user->email }}</p>
        </div>
    </div>
</body>
</html>
