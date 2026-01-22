<div class="space-y-8">
    <div class="grid lg:grid-cols-2 gap-6">
        <form action="{{ route('admin.printers.store') }}" method="POST" class="bg-slate-50 border border-slate-200 rounded-2xl p-6 space-y-4">
            @csrf
            <h4 class="text-lg font-semibold text-slate-900">Nueva impresora CloudPRNT</h4>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nombre</label>
                    <input type="text" name="name" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Modelo</label>
                    <input type="text" name="model" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="TSP100IV">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Ubicación</label>
                    <input type="text" name="location" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Cocina / Bar">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Device ID (opcional)</label>
                    <input type="text" name="device_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                </div>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="is_active" value="1" checked>
                Activa
            </label>
            <button type="submit" class="primary-button">Guardar impresora</button>
        </form>

        <form action="{{ route('admin.printers.templates.store') }}" method="POST" class="bg-slate-50 border border-slate-200 rounded-2xl p-6 space-y-4">
            @csrf
            <h4 class="text-lg font-semibold text-slate-900">Nueva plantilla</h4>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nombre</label>
                    <input type="text" name="name" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tipo</label>
                    <select name="type" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="ticket">Ticket</option>
                        <option value="label">Label</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Contenido</label>
                <textarea name="body" rows="6" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>Orden @{{order_id}}
Mesa @{{table}} · @{{guest}} (@{{party_size}})
@{{items}}</textarea>
            </div>
            <p class="text-xs text-slate-500">Variables: @{{order_id}}, @{{table}}, @{{guest}}, @{{party_size}}, @{{items}}, @{{item_name}}, @{{item_qty}}, @{{item_notes}}, @{{item_extras}}.</p>
            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="is_active" value="1" checked>
                Activa
            </label>
            <button type="submit" class="primary-button">Guardar plantilla</button>
        </form>
    </div>

    <div class="space-y-4">
        <h4 class="text-lg font-semibold text-slate-900">Impresoras registradas</h4>
        <div class="grid md:grid-cols-2 gap-4">
            @forelse($printers as $printer)
                <div class="border border-slate-200 rounded-2xl p-4 space-y-3">
                    <form action="{{ route('admin.printers.update', $printer) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Nombre</label>
                                <input type="text" name="name" value="{{ $printer->name }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Modelo</label>
                                <input type="text" name="model" value="{{ $printer->model }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Ubicación</label>
                                <input type="text" name="location" value="{{ $printer->location }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Device ID</label>
                                <input type="text" name="device_id" value="{{ $printer->device_id }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            </div>
                        </div>
                        <div class="text-xs text-slate-500">Token: <span class="font-mono">{{ $printer->token }}</span></div>
                        <div class="text-xs text-slate-500">Último ping: {{ $printer->last_seen_at ? $printer->last_seen_at->format('Y-m-d H:i') : 'sin ping' }}</div>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="is_active" value="1" {{ $printer->is_active ? 'checked' : '' }}>
                            Activa
                        </label>
                        <button type="submit" class="primary-button">Actualizar</button>
                    </form>
                    <form action="{{ route('admin.printers.destroy', $printer) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ghost-button">Eliminar</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-slate-500">Sin impresoras todavía.</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-4">
        <h4 class="text-lg font-semibold text-slate-900">Plantillas</h4>
        <div class="grid md:grid-cols-2 gap-4">
            @forelse($printTemplates as $template)
                <div class="border border-slate-200 rounded-2xl p-4 space-y-3">
                    <form action="{{ route('admin.printers.templates.update', $template) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Nombre</label>
                                <input type="text" name="name" value="{{ $template->name }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Tipo</label>
                                <select name="type" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <option value="ticket" {{ $template->type === 'ticket' ? 'selected' : '' }}>Ticket</option>
                                    <option value="label" {{ $template->type === 'label' ? 'selected' : '' }}>Label</option>
                                </select>
                            </div>
                        </div>
                        <textarea name="body" rows="5" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $template->body }}</textarea>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="is_active" value="1" {{ $template->is_active ? 'checked' : '' }}>
                            Activa
                        </label>
                        <button type="submit" class="primary-button">Actualizar</button>
                    </form>
                    <form action="{{ route('admin.printers.templates.destroy', $template) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ghost-button">Eliminar</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-slate-500">Sin plantillas todavía.</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-4">
        <h4 class="text-lg font-semibold text-slate-900">Rutas de impresión</h4>
        <form action="{{ route('admin.printers.routes.store') }}" method="POST" class="bg-slate-50 border border-slate-200 rounded-2xl p-6 space-y-4">
            @csrf
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Impresora</label>
                    <select name="printer_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                        @foreach($printers as $printer)
                            <option value="{{ $printer->id }}">{{ $printer->name }} ({{ $printer->location }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Plantilla</label>
                    <select name="print_template_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                        @foreach($printTemplates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }} ({{ $template->type }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Scope</label>
                    <select name="category_scope" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="all">Todo</option>
                        <option value="menu">Menú</option>
                        <option value="cocktails">Cócteles</option>
                        <option value="wines">Bebidas</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ID de categoría (opcional)</label>
                    <input type="number" name="category_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Ej. 3">
                </div>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="enabled" value="1" checked>
                Ruta activa
            </label>
            <button type="submit" class="primary-button">Guardar ruta</button>
            <div class="text-xs text-slate-500">
                IDs disponibles: Menú {{ $categories->pluck('id')->implode(', ') }} ·
                Cócteles {{ $cocktailCategories->pluck('id')->implode(', ') }} ·
                Bebidas {{ $wineCategories->pluck('id')->implode(', ') }}
            </div>
        </form>

        <div class="grid md:grid-cols-2 gap-4">
            @forelse($printerRoutes as $route)
                <div class="border border-slate-200 rounded-2xl p-4 space-y-2">
                    <div class="text-sm font-semibold text-slate-900">{{ $route->printer?->name }} → {{ $route->template?->name }}</div>
                    <div class="text-xs text-slate-500">Scope: {{ $route->category_scope }} @if($route->category_id) · ID {{ $route->category_id }} @endif</div>
                    <div class="text-xs text-slate-500">Estado: {{ $route->enabled ? 'Activa' : 'Inactiva' }}</div>
                    <form action="{{ route('admin.printers.routes.destroy', $route) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ghost-button">Eliminar</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-slate-500">Sin rutas todavía.</p>
            @endforelse
        </div>
    </div>
</div>
