@extends('layouts.admin')

@section('title', 'Crear especial')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Nuevo especial</h1>
            <p class="text-sm text-slate-500">Define horarios y selecciona categorías e items visibles.</p>
        </div>
        <a href="{{ route('admin.specials.index') }}" class="ghost-button w-auto px-5">Volver</a>
    </div>

    @if($errors->any())
        <div class="rounded-2xl bg-rose-50 text-rose-700 px-4 py-3 text-sm border border-rose-200">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.specials.store') }}" method="POST" class="space-y-8">
        @csrf
        @include('admin.specials._form', compact('special', 'scopes', 'selectedCategories', 'selectedItems', 'days'))
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="primary-button w-auto px-6">Guardar especial</button>
            <a href="{{ route('admin.specials.index') }}" class="ghost-button w-auto px-6">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const collapsePanels = {};
    const setExpanded = (target, expanded) => {
        const state = collapsePanels[target];
        if (!state) return;
        state.panel.classList.toggle('hidden', !expanded);
        if (state.button) {
            state.button.textContent = expanded ? 'Ocultar items' : 'Ver items';
            state.button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    };

    document.querySelectorAll('[data-collapse-panel]').forEach(panel => {
        const target = panel.dataset.collapsePanel;
        const button = document.querySelector(`[data-collapse-toggle="${target}"]`);
        collapsePanels[target] = { panel, button };
        const expanded = !panel.classList.contains('hidden');
        if (button) {
            button.textContent = expanded ? 'Ocultar items' : 'Ver items';
            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    });

    document.querySelectorAll('[data-collapse-toggle]').forEach(button => {
        const target = button.dataset.collapseToggle;
        button.addEventListener('click', () => {
            const state = collapsePanels[target];
            if (!state) return;
            const expanded = state.panel.classList.contains('hidden');
            setExpanded(target, expanded);
        });
    });

    document.querySelectorAll('[data-category-toggle]').forEach(input => {
        const target = input.dataset.categoryToggle;
        const itemsContainer = document.querySelector(`[data-category-items="${target}"]`);
        const sync = () => {
            if (!itemsContainer) return;
            if (input.checked) {
                itemsContainer.classList.remove('opacity-50');
                itemsContainer.querySelectorAll('input').forEach(el => el.disabled = false);
                setExpanded(target, true);
            } else {
                itemsContainer.classList.add('opacity-50');
                itemsContainer.querySelectorAll('input').forEach(el => el.disabled = true);
                setExpanded(target, false);
            }
        };
        sync();
        input.addEventListener('change', sync);
    });
</script>
@endpush
