@extends('layouts.admin')

@section('title', 'Crear evento especial')
@section('body-class', 'bg-slate-950 text-white font-sans antialiased')

@section('content')
<div class="space-y-8">
    <div class="glass-card flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-amber-400">Eventos</p>
            <h1 class="text-3xl font-semibold text-white mt-1">Nuevo evento especial</h1>
            <p class="text-sm text-slate-400">Define la narrativa, visuales y secciones para tu próxima experiencia.</p>
        </div>
        <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-2 px-5 py-2 rounded-full border border-white/10 text-sm text-slate-200 hover:bg-white/10 transition">
            ← Volver
        </a>
    </div>

    <form id="eventWizard" action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="wizard-nav glass-card flex flex-wrap gap-3">
            <button type="button" class="wizard-tab active" data-target="step1">1) Evento</button>
            <button type="button" class="wizard-tab" data-target="step2">2) Layout</button>
            <button type="button" class="wizard-tab" data-target="step3">3) Publicación</button>
        </div>

        <div class="glass-card space-y-6">
            <div id="step1" class="wizard-panel active space-y-5">
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="input-label">Título</label>
                        <input type="text" name="title" class="input-control" required data-slug-source="#createSlug" value="{{ old('title') }}">
                    </div>
                    <div>
                        <label class="input-label">Slug</label>
                        <input type="text" id="createSlug" name="slug" class="input-control" required value="{{ old('slug') }}">
                    </div>
                </div>
                <div>
                    <label class="input-label">Descripción</label>
                    <textarea name="description" rows="4" class="input-control">{{ old('description') }}</textarea>
                </div>
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="input-label">Inicio</label>
                        <input type="datetime-local" name="start_at" class="input-control" required value="{{ old('start_at') }}">
                    </div>
                    <div>
                        <label class="input-label">Fin</label>
                        <input type="datetime-local" name="end_at" class="input-control" value="{{ old('end_at') }}">
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="input-label">Imagen principal</label>
                        <input type="file" name="hero_image" class="input-control">
                    </div>
                    <div>
                        <label class="input-label">Mapa base</label>
                        <input type="file" name="map_image" class="input-control">
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="input-label">Subtítulo</label>
                        <input type="text" name="additional_info[subtitle]" class="input-control" value="{{ old('additional_info.subtitle') }}">
                    </div>
                    <div>
                        <label class="input-label">Código de vestimenta</label>
                        <input type="text" name="additional_info[dress_code]" class="input-control" value="{{ old('additional_info.dress_code') }}">
                    </div>
                </div>
                <div>
                    <label class="input-label">Notas especiales</label>
                    <textarea name="additional_info[notes]" rows="3" class="input-control">{{ old('additional_info.notes') }}</textarea>
                </div>
            </div>

            <div id="step2" class="wizard-panel space-y-4">
                <p class="text-sm text-slate-400">Sube un mapa y define zonas para estimar secciones. Este paso es informativo; luego podrás crear secciones reales en la pantalla de edición.</p>
                <div class="grid lg:grid-cols-2 gap-5">
                    <div class="relative border border-dashed border-white/20 rounded-3xl h-80 flex items-center justify-center text-slate-500 text-sm">
                        <p>Mapa preliminar (mock). Arrastra marcadores libremente.</p>
                        <div class="absolute top-10 left-1/2 -translate-x-1/2 badge">Zona A</div>
                        <div class="absolute bottom-10 left-1/3 badge">Zona VIP</div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="input-label">Nombre de la sección</label>
                            <input type="text" class="input-control" placeholder="Ej. Terraza Norte">
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Capacidad estimada</label>
                                <input type="number" class="input-control" placeholder="30">
                            </div>
                            <div>
                                <label class="input-label">Precio por invitado</label>
                                <input type="number" class="input-control" placeholder="150">
                            </div>
                        </div>
                        <div>
                            <label class="input-label">Descripción/beneficios</label>
                            <textarea class="input-control" rows="3" placeholder="Vista, amenities, etc."></textarea>
                        </div>
                        <button type="button" class="ghost-button w-full">Añadir sección (mock)</button>
                    </div>
                </div>
            </div>

            <div id="step3" class="wizard-panel space-y-4">
                <div class="flex items-center gap-3">
                    <input class="w-5 h-5 rounded border-white/30 bg-white/5 text-amber-500 focus:ring-amber-400" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                    <label for="is_active" class="text-sm text-slate-200">Publicar evento al guardar</label>
                </div>
                <div class="p-4 rounded-2xl bg-blue-500/10 border border-blue-500/30 text-slate-100 text-sm">
                    Una vez guardes, podrás añadir secciones reales, generar taquillas y compartir el enlace público.
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.events.index') }}" class="px-5 py-2 rounded-full border border-white/10 text-sm text-slate-300 hover:bg-white/10 transition">Cancelar</a>
                    <button type="submit" class="px-6 py-2 rounded-full bg-amber-500 text-slate-900 font-semibold shadow-lg shadow-amber-500/30 hover:bg-amber-400 transition">Guardar evento</button>
                </div>
            </div>
        </div>
    </form>
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
    .wizard-tab {
        padding: 0.6rem 1.5rem;
        border-radius: 9999px;
        border: 1px solid transparent;
        background: rgba(255, 255, 255, 0.04);
        color: #cbd5f5;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all .2s;
    }
    .wizard-tab.active {
        background: rgba(251, 191, 36, 0.1);
        border-color: rgba(251, 191, 36, 0.4);
        color: #fcd34d;
    }
    .wizard-panel {
        display: none;
    }
    .wizard-panel.active {
        display: block;
    }
    .badge {
        padding: 0.35rem 0.9rem;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(15, 23, 42, 0.7);
        color: #e2e8f0;
        font-size: 0.75rem;
    }
    .ghost-button {
        border: 1px dashed rgba(255, 255, 255, 0.3);
        border-radius: 9999px;
        padding: 0.65rem 1rem;
        color: #e2e8f0;
    }
</style>
@endpush

@push('scripts')
<script>
    const wizardTabs = document.querySelectorAll('.wizard-tab');
    const wizardPanels = document.querySelectorAll('.wizard-panel');
    const eventForm = document.getElementById('eventWizard');

    function slugify(text) {
        return text.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    }

    document.querySelectorAll('[data-slug-source]').forEach(input => {
        const target = document.querySelector(input.dataset.slugSource);
        if (!target) return;
        input.addEventListener('input', () => {
            if (!target.dataset.touched) target.value = slugify(input.value);
        });
        target.addEventListener('input', () => target.dataset.touched = true);
    });

    function openStep(targetId) {
        wizardTabs.forEach(tab => tab.classList.toggle('active', tab.dataset.target === targetId));
        wizardPanels.forEach(panel => panel.classList.toggle('active', panel.id === targetId));
    }

    wizardTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            if (tab.dataset.target !== 'step1' || validateStep1()) {
                openStep(tab.dataset.target);
            }
        });
    });

    function validateStep1() {
        let valid = true;
        eventForm.querySelectorAll('#step1 [required]').forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('border-red-500');
                valid = false;
            } else {
                input.classList.remove('border-red-500');
            }
        });
        return valid;
    }
</script>
@endpush
