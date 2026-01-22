<div class="space-y-8">
    <form action="{{ route('admin.taxes.store') }}" method="POST" class="bg-slate-50 border border-slate-200 rounded-2xl p-6 space-y-4">
        @csrf
        <h4 class="text-lg font-semibold text-slate-900">Nuevo impuesto</h4>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nombre</label>
                <input type="text" name="name" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Estatal 7%" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Porcentaje</label>
                <input type="number" name="rate" step="0.01" min="0" max="100" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="7.00" required>
            </div>
            <div class="flex items-center">
                <label class="inline-flex items-center gap-2 text-sm text-slate-600 mt-6">
                    <input type="checkbox" name="active" value="1" checked>
                    Activo
                </label>
            </div>
        </div>
        <button type="submit" class="primary-button">Guardar impuesto</button>
    </form>

    <div class="space-y-4">
        <h4 class="text-lg font-semibold text-slate-900">Impuestos registrados</h4>
        <div class="grid md:grid-cols-2 gap-4">
            @forelse($taxes as $tax)
                <div class="border border-slate-200 rounded-2xl p-4 space-y-3">
                    <form action="{{ route('admin.taxes.update', $tax) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Nombre</label>
                                <input type="text" name="name" value="{{ $tax->name }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Porcentaje</label>
                                <input type="number" name="rate" step="0.01" min="0" max="100" value="{{ $tax->rate }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                            </div>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="active" value="1" {{ $tax->active ? 'checked' : '' }}>
                            Activo
                        </label>
                        <button type="submit" class="primary-button">Actualizar</button>
                    </form>
                    <form action="{{ route('admin.taxes.destroy', $tax) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ghost-button">Eliminar</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-slate-500">Sin impuestos todavía.</p>
            @endforelse
        </div>
    </div>
</div>
