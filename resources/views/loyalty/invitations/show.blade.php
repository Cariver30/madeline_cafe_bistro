<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Activa tu acceso</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-white flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white/10 border border-white/20 rounded-3xl p-8 backdrop-blur">
        <p class="text-xs uppercase tracking-[0.3em] text-amber-300 mb-4">Accesos Madeline Bistro</p>
        <h1 class="text-2xl font-semibold mb-2">Configura tu contraseña</h1>
        <p class="text-white/70 text-sm mb-6">
            Acceso para {{ $roleLabel ?? 'mesero' }}. Ingresa una contraseña segura para continuar.
        </p>

        <form method="POST" action="{{ route('loyalty.invitations.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="email" value="{{ request('email') }}">
            <input type="hidden" name="token" value="{{ request('token') }}">
            <div>
                <label class="text-xs uppercase tracking-[0.3em] text-white/60 block mb-2">Correo</label>
                <input type="email" value="{{ request('email') }}" disabled class="w-full rounded-2xl bg-white/10 border border-white/20 px-4 py-3 text-white">
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.3em] text-white/60 block mb-2">Contraseña</label>
                <input type="password" name="password" required minlength="8" class="w-full rounded-2xl bg-white/10 border border-white/20 px-4 py-3 text-white">
                @error('password')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.3em] text-white/60 block mb-2">Confirmar</label>
                <input type="password" name="password_confirmation" required class="w-full rounded-2xl bg-white/10 border border-white/20 px-4 py-3 text-white">
            </div>
            <button type="submit" class="w-full rounded-full bg-amber-400 text-slate-950 font-semibold py-3 mt-4">Guardar contraseña</button>
        </form>
    </div>
</body>
</html>
