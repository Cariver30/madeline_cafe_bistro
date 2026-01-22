<div class="space-y-8">
    <div class="grid lg:grid-cols-2 gap-6">
        <form action="{{ route('admin.prep.areas.store') }}" method="POST" class="bg-slate-50 border border-slate-200 rounded-2xl p-6 space-y-4">
            @csrf
            <h4 class="text-lg font-semibold text-slate-900">Nueva área</h4>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nombre</label>
                    <input type="text" name="name" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Color (hex)</label>
                    <input type="text" name="color" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="#fbbf24">
                </div>
            </div>
            <div class="flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="active" value="1" checked>
                    Activa
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="is_default" value="1">
                    Área por defecto
                </label>
            </div>
            <button type="submit" class="primary-button">Guardar área</button>
        </form>

        <form action="{{ route('admin.prep.labels.store') }}" method="POST" class="bg-slate-50 border border-slate-200 rounded-2xl p-6 space-y-4">
            @csrf
            <h4 class="text-lg font-semibold text-slate-900">Nuevo label</h4>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nombre</label>
                    <input type="text" name="name" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Área</label>
                    <select name="prep_area_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                        @foreach($prepAreas as $area)
                            <option value="{{ $area->id }}" {{ $area->is_default ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Impresora (opcional)</label>
                    <select name="printer_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Sin impresora</option>
                        @foreach($printers as $printer)
                            <option value="{{ $printer->id }}">
                                {{ $printer->name }} ({{ $printer->location }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="active" value="1" checked>
                Activo
            </label>
            <button type="submit" class="primary-button">Guardar label</button>
        </form>
    </div>

    <div class="space-y-4">
        <h4 class="text-lg font-semibold text-slate-900">Áreas registradas</h4>
        <div class="grid md:grid-cols-2 gap-4">
            @forelse($prepAreas as $area)
                <div class="border border-slate-200 rounded-2xl p-4 space-y-3">
                    <form action="{{ route('admin.prep.areas.update', $area) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Nombre</label>
                                <input type="text" name="name" value="{{ $area->name }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Color</label>
                                <input type="text" name="color" value="{{ $area->color }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="active" value="1" {{ $area->active ? 'checked' : '' }}>
                                Activa
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="is_default" value="1" {{ $area->is_default ? 'checked' : '' }}>
                                Área por defecto
                            </label>
                        </div>
                        <button type="submit" class="primary-button">Actualizar</button>
                    </form>
                    <form action="{{ route('admin.prep.areas.destroy', $area) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ghost-button">Eliminar</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-slate-500">Sin áreas todavía.</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-4">
        <h4 class="text-lg font-semibold text-slate-900">Labels registrados</h4>
        <div class="grid md:grid-cols-2 gap-4">
            @forelse($prepLabels as $label)
                <div class="border border-slate-200 rounded-2xl p-4 space-y-3">
                    <form action="{{ route('admin.prep.labels.update', $label) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Nombre</label>
                                <input type="text" name="name" value="{{ $label->name }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Área</label>
                                <select name="prep_area_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    @foreach($prepAreas as $area)
                                        <option value="{{ $area->id }}" {{ $label->prep_area_id === $area->id ? 'selected' : '' }}>
                                            {{ $area->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs text-slate-500 mb-1">Impresora</label>
                                <select name="printer_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <option value="">Sin impresora</option>
                                    @foreach($printers as $printer)
                                        <option value="{{ $printer->id }}" {{ $label->printer_id === $printer->id ? 'selected' : '' }}>
                                            {{ $printer->name }} ({{ $printer->location }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="active" value="1" {{ $label->active ? 'checked' : '' }}>
                            Activo
                        </label>
                        <button type="submit" class="primary-button">Actualizar</button>
                    </form>
                    <form action="{{ route('admin.prep.labels.destroy', $label) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ghost-button">Eliminar</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-slate-500">Sin labels todavía.</p>
            @endforelse
        </div>
    </div>
</div>
