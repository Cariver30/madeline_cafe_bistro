@php
    use Illuminate\Support\Facades\Storage;
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->title }} · Experiencia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #050914;
            color: #e2e8f0;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }
        .hero {
            background-size: cover;
            background-position: center;
            border-radius: 32px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.15);
            box-shadow: 0 30px 60px rgba(0,0,0,0.45);
        }
        .glass {
            background: rgba(7, 11, 26, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            backdrop-filter: blur(18px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.45);
        }
        .label {
            font-size: 0.75rem;
            letter-spacing: 0.45em;
            text-transform: uppercase;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="max-w-6xl mx-auto px-4 py-10 space-y-10">
        <div class="flex flex-wrap gap-3 justify-between items-center text-sm text-slate-300">
            <a href="{{ route('experiences.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/10 hover:bg-white/10 transition">
                ← Todos los eventos
            </a>
            <a href="{{ route('cover') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/10 hover:bg-white/10 transition">
                Ir al inicio
            </a>
        </div>
        <header class="hero" style="background-image: linear-gradient(120deg, rgba(5,9,20,0.9), rgba(5,9,20,0.2)), url('{{ $event->hero_image ? Storage::url($event->hero_image) : asset('images/placeholder-event.jpg') }}');">
            <div class="p-10 md:p-14 flex flex-col gap-5 max-w-3xl">
                <p class="label">{{ $event->start_at->format('d M Y · H:i') }}</p>
                <h1 class="text-4xl md:text-5xl font-serif text-white">{{ $event->title }}</h1>
                @if($event->additional_info['subtitle'] ?? false)
                    <p class="text-amber-300 text-lg">{{ $event->additional_info['subtitle'] }}</p>
                @endif
                <p class="text-slate-200">{{ $event->description }}</p>
                <div class="flex flex-wrap gap-4 text-sm text-slate-300">
                    @if($event->additional_info['dress_code'] ?? false)
                        <span class="px-4 py-2 rounded-full bg-white/10 border border-white/20">Dress code: {{ $event->additional_info['dress_code'] }}</span>
                    @endif
                    @if($event->end_at)
                        <span class="px-4 py-2 rounded-full bg-white/10 border border-white/20">Finaliza {{ $event->end_at->format('d M Y H:i') }}</span>
                    @endif
                </div>
            </div>
        </header>

        @if($ticket)
            <section class="glass p-6 space-y-4 border border-emerald-400/30">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <p class="label text-emerald-300">Tu reserva está confirmada</p>
                        <p class="text-xl font-semibold text-white">{{ $ticket['customer'] }}</p>
                    </div>
                    <span class="px-4 py-2 rounded-full bg-emerald-400/10 text-emerald-200 text-sm">Código: {{ $ticket['code'] }}</span>
                </div>
                <p class="text-sm text-slate-400">Presenta este QR al llegar para validar tus taquillas.</p>
                <div class="flex flex-wrap gap-6 items-center">
                    <div id="ticketQR" class="p-4 bg-black/30 rounded-2xl border border-white/5" data-code="{{ $ticket['code'] }}"></div>
                    <div class="text-sm text-slate-300 space-y-1">
                        <p>Sección: <strong>{{ $ticket['section'] }}</strong></p>
                        <p>Invitados: <strong>{{ $ticket['guest_count'] }}</strong></p>
                        <p>Total pagado: <strong>${{ number_format($ticket['total_paid'], 2) }}</strong></p>
                    </div>
                </div>
            </section>
        @endif

        @if(session('notification_success'))
            <section class="glass p-4 border border-amber-400/30 text-amber-200 text-sm">
                {{ session('notification_success') }}
            </section>
        @endif

        <div class="grid lg:grid-cols-3 gap-8">
            <section class="glass p-6 space-y-6 lg:col-span-2">
                <div>
                    <p class="label">Secciones disponibles</p>
                    <h2 class="text-2xl font-semibold text-white mt-2">Elige dónde vivir la experiencia</h2>
                </div>
                <div class="space-y-4">
                    @forelse($sections as $section)
                        <article class="border border-white/10 rounded-2xl p-4 bg-white/5">
                            <div class="flex flex-wrap justify-between gap-3">
                                <div>
                                    <p class="text-lg font-semibold text-white">{{ $section->name }}</p>
                                    <p class="text-sm text-slate-400">{{ $section->description }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-base text-amber-300 font-semibold">
                                        @if($section->flat_price)
                                            ${{ number_format($section->flat_price, 2) }} paquete
                                        @elseif($section->price_per_person)
                                            ${{ number_format($section->price_per_person, 2) }} / invitado
                                        @else
                                            A consultar
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $section->capacity }} capacidad</p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-slate-500">Pronto publicaremos las secciones de este evento.</p>
                    @endforelse
                </div>
            </section>

            <section class="glass p-6 space-y-5">
                <div>
                    <p class="label">Compra tus taquillas</p>
                    <h2 class="text-xl font-semibold text-white mt-2">Reserva digital</h2>
                </div>
                <form action="{{ route('experiences.purchase', $event) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm text-slate-300">Sección</label>
                        <select name="event_section_id" class="input-control mt-1" required>
                            <option value="">Selecciona una sección</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" @selected(old('event_section_id') == $section->id)>
                                    {{ $section->name }} —
                                    @if($section->flat_price)
                                        ${{ number_format($section->flat_price, 2) }} paquete
                                    @else
                                        ${{ number_format($section->price_per_person ?? 0, 2) }} / invitado
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('event_section_id')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm text-slate-300">Nombre completo</label>
                        <input type="text" name="customer_name" class="input-control mt-1" required value="{{ old('customer_name') }}">
                        @error('customer_name')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm text-slate-300">Correo electrónico</label>
                        <input type="email" name="customer_email" class="input-control mt-1" value="{{ old('customer_email') }}">
                        @error('customer_email')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm text-slate-300">Número de invitados</label>
                        <input type="number" min="1" max="20" name="guest_count" class="input-control mt-1" required value="{{ old('guest_count', 1) }}">
                        @error('guest_count')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm text-slate-300">Notas</label>
                        <textarea name="notes" rows="3" class="input-control mt-1">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" class="w-full px-5 py-3 rounded-full bg-amber-400 text-slate-900 font-semibold hover:bg-amber-300 transition">Generar taquillas digitales</button>
                </form>

                <div class="border-t border-white/10 pt-5">
                    <p class="label">Quiero noticias</p>
                    <h3 class="text-lg font-semibold text-white mt-2">Alertas del evento</h3>
                    <p class="text-sm text-slate-400 mb-3">Regístrate para recibir recordatorios, nuevas fechas o experiencias relacionadas.</p>
                    <form action="{{ route('experiences.notify', $event) }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="text" name="name" class="input-control" placeholder="Tu nombre" required value="{{ old('name') }}">
                        @error('name')
                            <p class="text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                        <input type="email" name="email" class="input-control" placeholder="Correo electrónico" required value="{{ old('email') }}">
                        @error('email')
                            <p class="text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="w-full px-5 py-2 rounded-full bg-white/10 hover:bg-white/20 transition text-sm font-semibold">Notificarme</button>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-TdC0BqzGwZqF03PPYMkS9YkzkzkeNw1ANC2sSNVbjuqx0zGUlAFjqZCkD6tGd9YuqbWtoyIRSVkxg7iu6z3f0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        const qrContainer = document.getElementById('ticketQR');
        if (qrContainer && qrContainer.dataset.code) {
            new QRCode(qrContainer, {
                text: qrContainer.dataset.code,
                width: 140,
                height: 140,
                colorDark : "#ffffff",
                colorLight : "transparent",
            });
        }
    </script>

    <style>
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
</body>
</html>
