@php
    use App\Models\Extra;

    $scopeLabels = [
        'global' => 'Todos',
        'menu' => 'Menú',
        'coffee' => 'Café & Brunch',
        'cocktails' => 'Cócteles',
    ];
    $redirectTarget = $redirectTo ?? route('admin.new-panel', ['section' => 'extras']);
@endphp

<div class="space-y-6 text-slate-900">
    <div class="grid lg:grid-cols-2 gap-6">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-black/5">
            <h4 class="text-lg font-semibold mb-2">Crear grupo de opciones</h4>
            <p class="text-sm text-slate-600 mb-4">Define add-ons reutilizables como “Aguacate”, “Extra shot” o “Salsa inglesa”.</p>
            <form action="{{ route('extras.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ $redirectTarget }}">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Título del grupo</label>
                    <input type="text" name="group_name" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" placeholder="Tipo de pan, Tipo de leche, Termino de la carne">
                </div>
                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo</label>
                        <select name="kind" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
                            <option value="modifier">Modificador</option>
                            <option value="extra">Adicional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Vista</label>
                        <select name="view_scope" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
                            @foreach(Extra::VIEW_SCOPES as $scope)
                                <option value="{{ $scope }}">{{ $scopeLabels[$scope] ?? ucfirst($scope) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Max selecciones</label>
                        <input type="number" name="max_select" min="1" max="99" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" placeholder="1">
                    </div>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="group_required" value="1" class="rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                    Requerido al ordenar
                </label>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-semibold text-slate-700">Items del grupo</label>
                        <button type="button" id="addOptionRow" class="text-xs font-semibold text-amber-600 hover:text-amber-400">Agregar item</button>
                    </div>
                    <div id="optionRows" class="space-y-3">
                        <div class="grid sm:grid-cols-3 gap-3 option-row">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre</label>
                                <input type="text" name="options[0][name]" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" placeholder="Aguacate, Extra shot">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Precio</label>
                                <input type="number" name="options[0][price]" step="0.01" min="0" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Descripción (opcional)</label>
                                <input type="text" name="options[0][description]" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" placeholder="Texto breve que veran los clientes.">
                            </div>
                        </div>
                    </div>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="active" value="1" checked class="rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                    Disponible inmediatamente
                </label>
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-amber-400 px-5 py-2.5 text-slate-900 font-semibold shadow-sm hover:bg-amber-300 transition">Guardar opciones</button>
                </div>
            </form>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-black/5">
            <h4 class="text-lg font-semibold mb-4">Información rápida</h4>
            <ul class="text-sm text-slate-600 space-y-2">
                <li>• Los extras “Global” aparecen en todas las vistas.</li>
                <li>• Puedes asignarlos a platos, cócteles y bebidas desde los formularios.</li>
                <li>• Si un extra está inactivo, se mantiene en los ítems existentes hasta que lo reemplaces.</li>
                <li>• En la app y el menú público se mostrará como “Opciones disponibles”.</li>
                <li>• Marca “Requerido” y define un máximo para controlar la selección.</li>
            </ul>
        </article>
    </div>

    <div class="space-y-6">
        @forelse($extras->groupBy('view_scope') as $scope => $items)
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-black/5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="uppercase text-xs tracking-[0.3em] text-slate-400 mb-1">Vista</p>
                        <h5 class="text-xl font-semibold text-slate-900">{{ $scopeLabels[$scope] ?? ucfirst($scope) }}</h5>
                    </div>
                    <span class="text-slate-500 text-sm">{{ $items->count() }} opciones</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-slate-700">
                        <thead>
                            <tr class="text-left text-slate-500 border-b border-slate-200">
                                <th class="pb-2">Nombre</th>
                                <th class="pb-2">Grupo</th>
                                <th class="pb-2">Tipo</th>
                                <th class="pb-2">Req.</th>
                                <th class="pb-2">Max</th>
                                <th class="pb-2">Precio</th>
                                <th class="pb-2 hidden md:table-cell">Descripción</th>
                                <th class="pb-2">Estado</th>
                                <th class="pb-2 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($items as $extra)
                                <tr>
                                    <td class="py-2 font-semibold text-slate-900">{{ $extra->name }}</td>
                                    <td class="py-2 text-slate-600">{{ $extra->group_name ?? 'Sin grupo' }}</td>
                                    <td class="py-2 text-slate-600">{{ $extra->kind === 'modifier' ? 'Modificador' : 'Adicional' }}</td>
                                    <td class="py-2 text-slate-600">{{ $extra->group_required ? 'Si' : 'No' }}</td>
                                    <td class="py-2 text-slate-600">{{ $extra->max_select ?: '—' }}</td>
                                    <td class="py-2 text-slate-700">${{ number_format($extra->price, 2) }}</td>
                                    <td class="py-2 hidden md:table-cell text-slate-500">{{ $extra->description ?: '—' }}</td>
                                    <td class="py-2">
                                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs {{ $extra->active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $extra->active ? 'Activo' : 'Archivado' }}
                                        </span>
                                    </td>
                                    <td class="py-2">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('extras.edit', $extra) }}?redirect_to={{ urlencode($redirectTarget) }}" class="text-amber-600 hover:text-amber-400 text-xs font-semibold">Editar</a>
                                            <form action="{{ route('extras.destroy', $extra) }}" method="POST" onsubmit="return confirm('¿Eliminar este extra?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="redirect_to" value="{{ $redirectTarget }}">
                                                <button type="submit" class="text-rose-600 hover:text-rose-400 text-xs font-semibold">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <p class="text-slate-600">Todavía no has creado extras.</p>
        @endforelse
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('optionRows');
        const addButton = document.getElementById('addOptionRow');
        if (!container || !addButton) return;

        let index = container.querySelectorAll('.option-row').length;
        addButton.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'grid sm:grid-cols-3 gap-3 option-row';
            row.innerHTML = `
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre</label>
                    <input type="text" name="options[${index}][name]" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" placeholder="Aguacate, Extra shot">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Precio</label>
                    <input type="number" name="options[${index}][price]" step="0.01" min="0" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" placeholder="0.00">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Descripción (opcional)</label>
                    <input type="text" name="options[${index}][description]" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" placeholder="Texto breve que veran los clientes.">
                </div>
            `;
            container.appendChild(row);
            index += 1;
        });
    });
</script>
