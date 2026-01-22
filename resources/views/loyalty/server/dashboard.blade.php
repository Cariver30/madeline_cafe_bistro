<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesero · Check-ins</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <header class="p-6 border-b border-white/10 flex justify-between items-center">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-amber-300">Programa de puntos</p>
            <h1 class="text-2xl font-semibold">Hola {{ auth()->user()->name }}</h1>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-sm text-white/70 hover:text-white">Salir</button>
        </form>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8 space-y-8">
        @if(session('success'))
            <div class="bg-emerald-500/15 border border-emerald-400/40 text-emerald-100 rounded-2xl px-4 py-3">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-rose-500/15 border border-rose-400/40 text-rose-100 rounded-2xl px-4 py-3">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="grid lg:grid-cols-2 gap-6">
            <article class="bg-white/5 border border-white/10 rounded-3xl p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-white/60 mb-3">Nueva visita</p>
                <form method="POST" action="{{ route('loyalty.visit.create') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs uppercase tracking-[0.3em] text-white/50 block mb-2">Nombre del invitado</label>
                        <input type="text" name="name" required class="w-full rounded-2xl bg-white/10 border border-white/20 px-4 py-3 text-white" value="{{ old('name') }}">
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-[0.3em] text-white/50 block mb-2">Correo</label>
                        <input type="email" name="email" required class="w-full rounded-2xl bg-white/10 border border-white/20 px-4 py-3 text-white" value="{{ old('email') }}">
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-[0.3em] text-white/50 block mb-2">Teléfono</label>
                        <input type="text" name="phone" required class="w-full rounded-2xl bg-white/10 border border-white/20 px-4 py-3 text-white" value="{{ old('phone') }}">
                    </div>
                    <button type="submit" class="w-full rounded-full bg-amber-400 text-slate-950 font-semibold py-3">Generar QR</button>
                </form>
                <p class="text-xs text-white/50 mt-4">Cada confirmación sumará {{ optional($settings)->loyalty_points_per_visit ?? 0 }} puntos.</p>
            </article>

            <article class="bg-white/5 border border-white/10 rounded-3xl p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-white/60 mb-3">QR activo</p>
                <div id="qrContainer" class="flex items-center justify-center h-64 bg-black/40 rounded-3xl border border-white/10">
                    <p class="text-white/40 text-center text-sm px-6">Genera un código y aparecerá aquí.</p>
                </div>
                <p class="text-xs text-white/50 mt-4">Comparte este QR al invitado para que confirme sus datos.</p>
            </article>
        </section>

        <section class="bg-white/5 border border-white/10 rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-white/60">Historial reciente</p>
                    <h2 class="text-xl font-semibold">Ultimas visitas</h2>
                </div>
            </div>
            <div class="space-y-3">
                @forelse($visits as $visit)
                    <article class="flex flex-wrap items-center justify-between gap-3 border border-white/10 rounded-2xl px-4 py-3">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-white/50">#{{ $visit->id }} · {{ $visit->status }}</p>
                            <h3 class="text-lg font-semibold">{{ $visit->expected_name }}</h3>
                            <p class="text-white/60 text-sm">{{ $visit->expected_email }} · {{ $visit->expected_phone }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-white/50">Puntos</p>
                            <p class="text-amber-300 text-2xl font-semibold">{{ $visit->points_awarded }}</p>
                        </div>
                    </article>
                @empty
                    <p class="text-white/60 text-sm">Aún no tienes visitas registradas.</p>
                @endforelse
            </div>
        </section>
    </main>

    <script>
        const qrContainer = document.getElementById('qrContainer');
        @if(session('active_visit'))
            qrContainer.innerHTML = '';
            new QRCode(qrContainer, {
                text: "{{ route('loyalty.visit.show', session('active_visit')) }}",
                width: 240,
                height: 240,
                colorDark : "#ffffff",
                colorLight : "#1f2937",
                correctLevel : QRCode.CorrectLevel.H
            });
        @endif
    </script>
</body>
</html>
