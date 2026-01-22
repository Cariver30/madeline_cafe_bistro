@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experiencias · {{ config('app.name', 'Café Negro') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: radial-gradient(circle at top, #1f2937, #0f172a);
            color: #e2e8f0;
            font-family: 'Inter', sans-serif;
        }
        .glass {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
            backdrop-filter: blur(18px);
        }
        .input-control {
            width: 100%;
            padding: 0.65rem 1rem;
            border-radius: 1rem;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
        }
        .input-control:focus {
            outline: 2px solid rgba(251, 191, 36, 0.6);
            border-color: rgba(251, 191, 36, 0.6);
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-10 space-y-10">
        <header class="glass p-8 space-y-6">
            <div class="flex flex-col gap-3">
                <p class="text-xs uppercase tracking-[0.35em] text-amber-400">Agenda exclusiva</p>
                <h1 class="text-4xl font-semibold text-white mt-2">Experiencias y eventos especiales</h1>
                <p class="text-slate-300 mt-4">Reserva una noche en nuestras experiencias temáticas. Selecciona tu evento para ver secciones, precios y comprar taquillas digitales.</p>
                <div>
                    <a href="{{ route('cover') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/20 text-sm text-slate-100 hover:bg-white/10 transition">
                        ← Volver al inicio
                    </a>
                </div>
            </div>
            @if(session('notification_success'))
                <div class="bg-emerald-500/10 border border-emerald-400/30 rounded-2xl p-4 text-sm text-emerald-100">
                    {{ session('notification_success') }}
                </div>
            @endif
            <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
                <p class="text-sm text-slate-300 mb-3">¿Quieres enterarte apenas anunciemos algo nuevo?</p>
                <form action="{{ route('experiences.notify.general') }}" method="POST" class="grid md:grid-cols-3 gap-3">
                    @csrf
                    <input type="text" name="name" class="input-control" placeholder="Tu nombre" required value="{{ old('name') }}">
                    <input type="email" name="email" class="input-control" placeholder="Tu correo" required value="{{ old('email') }}">
                    <button class="w-full px-5 py-2 rounded-full bg-amber-400 text-slate-900 font-semibold hover:bg-amber-300 transition">Avisarme</button>
                </form>
                <small class="text-xs text-slate-500">Te suscribiremos a las novedades del evento más próximo y futuras fechas.</small>
            </div>
        </header>

        <section class="grid gap-6 md:grid-cols-2">
            @forelse($events as $event)
                <article class="glass overflow-hidden flex flex-col">
                    <div class="h-48 overflow-hidden">
                        @if($event->hero_image)
                            <img src="{{ Storage::url($event->hero_image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-slate-800 flex items-center justify-center text-slate-500">Sin imagen</div>
                        @endif
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <p class="text-xs tracking-[0.3em] uppercase text-slate-400">{{ $event->start_at->format('d M Y · H:i') }}</p>
                        <h2 class="text-2xl font-semibold text-white mt-2">{{ $event->title }}</h2>
                        <p class="text-slate-400 mt-3 line-clamp-3">{{ Str::limit($event->description, 130) }}</p>
                        <div class="mt-auto pt-6">
                            <a href="{{ route('experiences.show', $event) }}" class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-amber-400 text-slate-900 font-semibold hover:bg-amber-300 transition">
                                Explorar evento →
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="md:col-span-2 text-center text-slate-400 py-12 glass">
                    Próximamente anunciaremos nuevas experiencias. Mantente atento.
                </div>
            @endforelse
        </section>
    </div>
</body>
</html>
