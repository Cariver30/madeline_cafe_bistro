@php
    use Illuminate\Support\Facades\Storage;
@endphp

@extends('layouts.admin')

@section('title', 'Editar evento · '.$event->title)
@section('body-class', 'bg-slate-950 text-white font-sans antialiased')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.4em] text-amber-400">Eventos</p>
            <h1 class="text-3xl font-semibold text-white mt-1">Editar: {{ $event->title }}</h1>
            <p class="text-sm text-slate-400">Actualiza información, imágenes y secciones desde un entorno cohesivo.</p>
        </div>
        <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-2 px-5 py-2 rounded-full border border-white/10 text-sm text-slate-200 hover:bg-white/10 transition">
            ← Volver al listado
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-200">
            <p class="font-semibold mb-2">Revisa los siguientes campos:</p>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid xl:grid-cols-3 gap-8">
        <section class="xl:col-span-2 space-y-6">
            <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data" class="glass-card space-y-6">
                @csrf
                @method('PUT')
                <div>
                    <h2 class="text-xl font-semibold text-white mb-1">Información principal</h2>
                    <p class="text-sm text-slate-400">Define los datos que verá el cliente en la portada del evento.</p>
                </div>
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="input-label">Título</label>
                        <input type="text" name="title" value="{{ old('title', $event->title) }}" class="input-control" required data-slug-source="#eventSlug">
                    </div>
                    <div>
                        <label class="input-label">Slug</label>
                        <input type="text" id="eventSlug" name="slug" value="{{ old('slug', $event->slug) }}" class="input-control" required>
                    </div>
                </div>
                <div>
                    <label class="input-label">Descripción</label>
                    <textarea name="description" rows="4" class="input-control">{{ old('description', $event->description) }}</textarea>
                </div>
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="input-label">Inicio</label>
                        <input type="datetime-local" name="start_at" value="{{ old('start_at', optional($event->start_at)->format('Y-m-d\TH:i')) }}" class="input-control" required>
                    </div>
                    <div>
                        <label class="input-label">Fin</label>
                        <input type="datetime-local" name="end_at" value="{{ old('end_at', optional($event->end_at)->format('Y-m-d\TH:i')) }}" class="input-control">
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="input-label">Imagen principal</label>
                        <input type="file" name="hero_image" class="input-control">
                        @if($event->hero_image)
                            <p class="text-xs text-slate-500 mt-1">Actual: {{ $event->hero_image }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="input-label">Mapa base</label>
                        <input type="file" name="map_image" class="input-control">
                        @if($event->map_image)
                            <p class="text-xs text-slate-500 mt-1">Actual: {{ $event->map_image }}</p>
                        @endif
                    </div>
                </div>
                <label class="inline-flex items-center gap-3 text-sm text-slate-200">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $event->is_active) ? 'checked' : '' }} class="w-5 h-5 rounded border-white/20 bg-white/5 text-amber-500 focus:ring-amber-400">
                    Evento visible públicamente
                </label>
                <hr class="border-white/5">
                <div>
                    <h3 class="text-lg font-semibold text-white mb-1">Información adicional</h3>
                    <p class="text-sm text-slate-400">Opcional: subtítulo, dress code y notas internas.</p>
                </div>
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="input-label">Subtítulo</label>
                        <input type="text" name="additional_info[subtitle]" value="{{ old('additional_info.subtitle', $event->additional_info['subtitle'] ?? '') }}" class="input-control">
                    </div>
                    <div>
                        <label class="input-label">Código de vestimenta</label>
                        <input type="text" name="additional_info[dress_code]" value="{{ old('additional_info.dress_code', $event->additional_info['dress_code'] ?? '') }}" class="input-control">
                    </div>
                </div>
                <div>
                    <label class="input-label">Notas o instrucciones</label>
                    <textarea name="additional_info[notes]" rows="3" class="input-control">{{ old('additional_info.notes', $event->additional_info['notes'] ?? '') }}</textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.events.index') }}" class="px-5 py-2 rounded-full border border-white/10 text-sm text-slate-300 hover:bg-white/10 transition">Cancelar</a>
                    <button type="submit" class="px-6 py-2 rounded-full bg-amber-500 text-slate-900 font-semibold shadow-lg shadow-amber-500/30 hover:bg-amber-400 transition">Guardar cambios</button>
                </div>
            </form>

            <div class="glass-card">
                <h3 class="text-lg font-semibold text-white mb-4">Mapa de referencia</h3>
                <div class="relative w-full rounded-3xl border border-white/10 overflow-hidden aspect-[4/3] bg-white/5">
                    @if($event->map_image)
                        <img src="{{ Storage::url($event->map_image) }}" alt="Mapa del evento" class="w-full h-full object-cover">
                    @else
                        <div class="flex items-center justify-center h-full text-slate-500 text-sm">Aún no hay un mapa cargado.</div>
                    @endif

                    @foreach($sections as $section)
                        @php $coords = $section->layout_coordinates ?? null; @endphp
                        @if($coords && isset($coords['top']) && isset($coords['left']))
                            <div class="absolute -translate-x-1/2 -translate-y-1/2 text-xs font-semibold bg-slate-900/80 border border-white/10 px-3 py-1 rounded-full shadow-lg" style="top: {{ $coords['top'] }}%; left: {{ $coords['left'] }}%;">
                                {{ $section->name }}
                            </div>
                        @endif
                    @endforeach
                </div>
                <p class="text-xs text-slate-500 mt-2">Trabajamos en porcentajes respecto al alto y ancho de la imagen base.</p>
            </div>
        </section>

        <section class="space-y-6">
            <div class="glass-card">
                <h3 class="text-lg font-semibold text-white mb-4">Secciones activas</h3>
                <div class="space-y-4">
                    @forelse($sections as $section)
                        <article class="border border-white/10 rounded-2xl p-4 bg-white/5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-white">{{ $section->name }}</p>
                                    <span class="text-xs inline-flex mt-1 px-3 py-1 rounded-full {{ $section->is_active ? 'bg-emerald-500/10 text-emerald-300' : 'bg-slate-700 text-slate-300' }}">{{ $section->is_active ? 'Activa' : 'Oculta' }}</span>
                                </div>
                                <form action="{{ route('admin.events.sections.destroy', $section) }}" method="POST" onsubmit="return confirm('¿Eliminar esta sección?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-300 hover:text-rose-200 text-lg">&times;</button>
                                </form>
                            </div>
                            <p class="text-xs text-slate-400 mt-2">{{ $section->description ?? 'Sin descripción.' }}</p>
                            <dl class="grid grid-cols-2 gap-3 text-xs text-slate-400 mt-3">
                                <div><span class="text-white font-semibold">{{ $section->capacity }}</span> capacidad</div>
                                <div><span class="text-white font-semibold">{{ $section->available_slots }}</span> lugares</div>
                                <div>{{ $section->price_per_person ? '$'.number_format($section->price_per_person, 2).' / pax' : 'Precio por pax N/D' }}</div>
                                <div>{{ $section->flat_price ? '$'.number_format($section->flat_price, 2).' fijo' : 'Precio fijo N/D' }}</div>
                            </dl>
                        </article>
                    @empty
                        <p class="text-sm text-slate-500">Aún no hay secciones registradas.</p>
                    @endforelse
                </div>
            </div>

            <div class="glass-card">
                <h3 class="text-lg font-semibold text-white mb-4">Añadir sección</h3>
                <form action="{{ route('admin.events.sections.store', $event) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="input-label">Nombre</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="input-control" required data-slug-source="#sectionSlug">
                    </div>
                    <div>
                        <label class="input-label">Slug</label>
                        <input type="text" id="sectionSlug" name="slug" value="{{ old('slug') }}" class="input-control" required>
                    </div>
                    <div>
                        <label class="input-label">Descripción</label>
                        <textarea name="description" rows="2" class="input-control">{{ old('description') }}</textarea>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="input-label">Capacidad</label>
                            <input type="number" min="1" name="capacity" value="{{ old('capacity') }}" class="input-control" required>
                        </div>
                        <div>
                            <label class="input-label">Lugares disponibles</label>
                            <input type="number" min="0" name="available_slots" value="{{ old('available_slots') }}" class="input-control" placeholder="auto">
                        </div>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="input-label">Precio por persona (USD)</label>
                            <input type="number" step="0.01" min="0" name="price_per_person" value="{{ old('price_per_person') }}" class="input-control">
                        </div>
                        <div>
                            <label class="input-label">Precio fijo (USD)</label>
                            <input type="number" step="0.01" min="0" name="flat_price" value="{{ old('flat_price') }}" class="input-control">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="input-label">Top (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="layout_coordinates[top]" value="{{ old('layout_coordinates.top') }}" class="input-control" placeholder="40">
                        </div>
                        <div>
                            <label class="input-label">Left (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="layout_coordinates[left]" value="{{ old('layout_coordinates.left') }}" class="input-control" placeholder="60">
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-200">
                        <input type="checkbox" name="is_active" value="1" class="w-4 h-4 rounded border-white/20 bg-white/5 text-emerald-500 focus:ring-emerald-400" {{ old('is_active', true) ? 'checked' : '' }}>
                        Mostrar sección
                    </label>
                    <button type="submit" class="w-full px-5 py-2 rounded-full bg-white/10 hover:bg-white/20 transition font-semibold text-sm">Crear sección</button>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
    .glass-card {
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 1.5rem;
        padding: 1.5rem;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.35);
        backdrop-filter: blur(18px);
    }
    .input-control {
        width: 100%;
        padding: 0.65rem 1rem;
        border-radius: 1.25rem;
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 0.9rem;
        color: #e2e8f0;
    }
    .input-control::placeholder {
        color: #64748b;
    }
    .input-control:focus {
        outline: 2px solid rgba(251, 191, 36, 0.6);
        border-color: rgba(251, 191, 36, 0.6);
    }
    .input-label {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        color: #94a3b8;
        margin-bottom: 0.35rem;
        font-weight: 600;
    }
    textarea.input-control {
        min-height: 3rem;
    }
</style>
@endpush

@push('scripts')
<script>
    function slugify(text) {
        return text
            .toString()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    document.querySelectorAll('[data-slug-source]').forEach(input => {
        const targetSelector = input.dataset.slugSource;
        const target = document.querySelector(targetSelector);
        if (!target) return;
        input.addEventListener('input', () => {
            if (!target.dataset.touched) {
                target.value = slugify(input.value);
            }
        });
        target.addEventListener('input', () => target.dataset.touched = true);
    });
</script>
@endpush
