<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirma tu visita</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-900 text-white flex items-center justify-center px-4">
    <div class="w-full max-w-lg bg-white/10 border border-white/20 rounded-3xl p-8 backdrop-blur">
        <p class="text-xs uppercase tracking-[0.35em] text-amber-300 mb-2">Check-in autorizado</p>
        <h1 class="text-3xl font-semibold mb-4">Confirma tu visita</h1>
        <p class="text-white/70 mb-6 text-sm">Ingresa tus datos tal cual los registró el mesero para sumar puntos a tu perfil.</p>

        @if ($errors->any())
            <div class="bg-rose-500/20 border border-rose-300/40 text-rose-200 text-sm rounded-2xl px-4 py-3 mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('loyalty.visit.store', $visit->qr_token) }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs uppercase tracking-[0.3em] text-white/60 block mb-2">Nombre completo</label>
                <input type="text" name="name" class="w-full rounded-2xl bg-white/10 border border-white/20 px-4 py-3 text-white" required value="{{ old('name') }}">
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.3em] text-white/60 block mb-2">Correo</label>
                <input type="email" name="email" class="w-full rounded-2xl bg-white/10 border border-white/20 px-4 py-3 text-white" required value="{{ old('email') }}">
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.3em] text-white/60 block mb-2">Teléfono</label>
                <input type="text" name="phone" class="w-full rounded-2xl bg-white/10 border border-white/20 px-4 py-3 text-white" required value="{{ old('phone') }}">
            </div>
            <button type="submit" class="w-full rounded-full bg-amber-400 text-slate-950 font-semibold py-3 mt-2">Confirmar visita</button>
        </form>
        <p class="text-xs text-white/50 mt-6">Autorización #{{ $visit->id }} · expira cuando el mesero cierre la visita.</p>
    </div>
</body>
</html>
