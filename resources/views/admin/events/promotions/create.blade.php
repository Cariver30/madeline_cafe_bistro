@extends('layouts.admin')

@section('title', 'Nueva campaña')
@section('body-class', 'bg-slate-950 text-white font-sans antialiased')

@section('content')
<div class="space-y-6">
    <div class="glass-card">
        <p class="text-xs uppercase tracking-[0.35em] text-amber-400 mb-2">Marketing</p>
        <h1 class="text-3xl font-semibold text-white">Crear campaña promocional</h1>
        <p class="text-sm text-slate-400">Carga tu hero, arrastra assets (PDF, GIF, videos) y redacta el mensaje para la lista VIP.</p>
    </div>

    @if ($errors->any())
        <div class="glass-card border border-rose-500/40 text-rose-200 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.events.promotions.store') }}" method="POST" enctype="multipart/form-data" class="glass-card space-y-6">
        @csrf
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="input-label">Título interno</label>
                <input type="text" name="title" class="input-control" required value="{{ old('title') }}">
            </div>
            <div>
                <label class="input-label">Asunto del correo</label>
                <input type="text" name="subject" class="input-control" required value="{{ old('subject') }}">
            </div>
        </div>
        <div>
            <label class="input-label">Texto de vista previa</label>
            <input type="text" name="preview_text" class="input-control" value="{{ old('preview_text') }}" placeholder="Opcional">
        </div>
        <div>
            <label class="input-label">Imagen principal (hero)</label>
            <input type="file" name="hero_image" class="input-control">
        </div>
        <div>
            <label class="input-label">Contenido (se transforma a HTML automáticamente)</label>
            <textarea name="body_html" rows="8" class="input-control" required placeholder="Escribe texto plano o pega HTML. Nosotros lo convertimos a párrafos automáticamente.">{{ old('body_html') }}</textarea>
            <p class="text-xs text-slate-400 mt-2">Tip: si solo escribes texto normal, lo transformaremos en párrafos HTML con el template por defecto.</p>
        </div>
        <div>
            <label class="input-label">Assets (PDF, GIF, videos)</label>
            <label class="dropzone" id="assetDropzone">
                <input type="file" name="assets[]" multiple class="hidden" id="assetInput">
                <span class="text-sm text-slate-300">Arrastra archivos o toca para seleccionar</span>
            </label>
            <ul id="assetList" class="mt-2 text-sm text-slate-400 space-y-1"></ul>
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-200">
            <input type="checkbox" name="send_now" value="1" class="w-4 h-4 rounded border-white/30 bg-white/5">
            Enviar de inmediato al guardar
        </label>
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.events.promotions.index') }}" class="px-5 py-2 rounded-full border border-white/10 text-sm text-slate-300 hover:bg-white/10">Cancelar</a>
            <button type="submit" class="px-6 py-2 rounded-full bg-amber-500 text-slate-900 font-semibold hover:bg-amber-400 transition">Guardar campaña</button>
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
        padding: 1.75rem;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.35);
        backdrop-filter: blur(18px);
    }
    .input-control {
        width: 100%;
        padding: 0.65rem 1rem;
        border-radius: 1.25rem;
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #e2e8f0;
    }
    .input-control:focus {
        outline: 2px solid rgba(251, 191, 36, 0.6);
        border-color: rgba(251, 191, 36, 0.6);
    }
    .input-label {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.35em;
        color: #94a3b8;
        margin-bottom: 0.4rem;
    }
    .dropzone {
        border: 1px dashed rgba(255, 255, 255, 0.3);
        border-radius: 1.25rem;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        display: block;
    }
</style>
@endpush

@push('scripts')
<script>
    const dropzone = document.getElementById('assetDropzone');
    const input = document.getElementById('assetInput');
    const list = document.getElementById('assetList');

    dropzone.addEventListener('click', () => input.click());
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('bg-white/10');
    });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('bg-white/10'));
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('bg-white/10');
        input.files = e.dataTransfer.files;
        renderList();
    });
    input.addEventListener('change', renderList);

    function renderList() {
        list.innerHTML = '';
        Array.from(input.files).forEach(file => {
            const li = document.createElement('li');
            li.textContent = `${file.name} · ${(file.size / 1024 / 1024).toFixed(2)} MB`;
            list.appendChild(li);
        });
    }
</script>
@endpush
