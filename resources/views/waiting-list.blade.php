<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $accent = $settings?->button_color_cover ?? '#f59e0b';
        $bgImage = $settings?->background_image_cover
            ? asset('storage/' . $settings->background_image_cover)
            : null;
        $estimatedWait = $waitSettings->default_wait_minutes ?? 15;
        $restaurantName = $settings?->cover_hero_title ?: 'Madeline Cafe Bistro';
    @endphp
    <title>Lista de espera · {{ $restaurantName }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:400,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --accent: {{ $accent }};
        }
        body {
            font-family: 'Manrope', sans-serif;
        }
        .headline {
            font-family: 'Fraunces', serif;
        }
        .glass {
            background: rgba(15, 23, 42, 0.75);
            border: 1px solid rgba(148, 163, 184, 0.2);
            box-shadow: 0 30px 80px rgba(2, 6, 23, 0.45);
            backdrop-filter: blur(12px);
        }
        .accent {
            background: var(--accent);
        }
        .accent-text {
            color: var(--accent);
        }
        .cta-button {
            background: #0b0f1a;
            color: #f8fafc;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <div class="fixed inset-0">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-black"></div>
        @if($bgImage)
            <div class="absolute inset-0 opacity-30">
                <img src="{{ $bgImage }}" alt="Fondo" class="w-full h-full object-cover">
            </div>
        @endif
        <div class="absolute -top-32 right-0 h-80 w-80 rounded-full bg-amber-400/20 blur-3xl"></div>
        <div class="absolute -bottom-40 left-0 h-96 w-96 rounded-full bg-sky-500/10 blur-3xl"></div>
    </div>

    <main class="relative min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-2xl">
            <div class="glass rounded-3xl p-8 md:p-10">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Lista de espera</p>
                        <h1 class="headline text-3xl md:text-4xl font-bold mt-2">
                            Reserva tu lugar en {{ $restaurantName }}
                        </h1>
                    </div>
                    <div class="hidden md:flex items-center gap-2 text-sm text-slate-300">
                        <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                        Estimado: {{ $estimatedWait }} min
                    </div>
                </div>

                <p class="mt-4 text-slate-300 leading-relaxed">
                    Completa tus datos y el host te avisará cuando tu mesa esté lista.
                </p>

                @if(session('success'))
                    <div class="mt-6 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mt-6 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-rose-200">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('waiting-list.store') }}" method="POST" class="mt-8 grid gap-5">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-sm text-slate-300">Nombre</label>
                            <input type="text" name="guest_name" value="{{ old('guest_name') }}" required
                                   class="mt-2 w-full rounded-2xl bg-slate-900/80 border border-slate-700 px-4 py-3 focus:border-amber-400 focus:outline-none" />
                        </div>
                        <div>
                            <label class="text-sm text-slate-300">Teléfono</label>
                            <input type="tel" name="guest_phone" value="{{ old('guest_phone') }}" required
                                   class="mt-2 w-full rounded-2xl bg-slate-900/80 border border-slate-700 px-4 py-3 focus:border-amber-400 focus:outline-none" />
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-sm text-slate-300">Correo (opcional)</label>
                            <input type="email" name="guest_email" value="{{ old('guest_email') }}"
                                   class="mt-2 w-full rounded-2xl bg-slate-900/80 border border-slate-700 px-4 py-3 focus:border-amber-400 focus:outline-none" />
                        </div>
                        <div>
                            <label class="text-sm text-slate-300">Personas</label>
                            <input type="number" name="party_size" min="1" max="99" value="{{ old('party_size', 2) }}" required
                                   class="mt-2 w-full rounded-2xl bg-slate-900/80 border border-slate-700 px-4 py-3 focus:border-amber-400 focus:outline-none" />
                        </div>
                    </div>
                    <div>
                        <label class="text-sm text-slate-300">Notas (opcional)</label>
                        <textarea name="notes" rows="3"
                                  class="mt-2 w-full rounded-2xl bg-slate-900/80 border border-slate-700 px-4 py-3 focus:border-amber-400 focus:outline-none">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="text-sm text-slate-400">
                            Tiempo estimado: <span class="accent-text font-semibold">{{ $estimatedWait }} min</span>
                        </div>
                        <button type="submit" class="cta-button font-semibold px-6 py-3 rounded-2xl hover:opacity-90 transition">
                            Unirme a la lista
                        </button>
                    </div>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-slate-500">
                Responde “CANCELAR” al SMS si ya no vienes. Te avisaremos cuando esté tu mesa.
            </p>
        </div>
    </main>
</body>
</html>
