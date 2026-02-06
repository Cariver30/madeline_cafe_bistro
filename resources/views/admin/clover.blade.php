@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="mb-1">Integración Clover</h2>
                <p class="text-muted mb-0">Asigna cada categoría Clover a la vista correcta (Menú, Cócteles, Vinos o Cantina).</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.clover.sync_all') }}" class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
                    @csrf
                    <button class="btn btn-dark">Sync Clover (todo)</button>
                    <input type="hidden" name="sync_taxes" value="1">
                    <span class="text-muted small d-flex align-items-center">Taxes Clover siempre activos</span>
                </form>
                <form method="POST" action="{{ route('admin.clover.categories.sync') }}">
                    @csrf
                    <button class="btn btn-outline-secondary">Actualizar categorías</button>
                </form>
                <form method="POST" action="{{ route('admin.clover.items.sync') }}" class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
                    @csrf
                    <button class="btn btn-dark">Importar items</button>
                    <input type="hidden" name="sync_taxes" value="1">
                    <span class="text-muted small d-flex align-items-center">Taxes Clover siempre activos</span>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @php
            $scopeLabels = $viewLabels ?? [
                'menu' => $settings->tab_label_menu ?? $settings->button_label_menu ?? 'Menú',
                'cocktails' => $settings->tab_label_cocktails ?? $settings->button_label_cocktails ?? 'Cócteles',
                'wines' => $settings->tab_label_wines ?? $settings->button_label_wines ?? 'Café & Brunch',
                'cantina' => $settings->tab_label_cantina ?? $settings->button_label_cantina ?? 'Cantina',
            ];
        @endphp

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-2">Crear categoría interna (para agrupar Clover)</h5>
                <p class="text-muted small mb-3">Crea una categoría padre como “Postres” sin salir del mapeo Clover.</p>
                <form method="POST" action="{{ route('admin.clover.parent-categories.store') }}" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">Vista</label>
                        <select name="parent_scope" class="form-select" required>
                            <option value="menu">{{ $scopeLabels['menu'] }}</option>
                            <option value="cocktails">{{ $scopeLabels['cocktails'] }}</option>
                            <option value="wines">{{ $scopeLabels['wines'] }}</option>
                            <option value="cantina">{{ $scopeLabels['cantina'] }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nombre de la categoría</label>
                        <input type="text" name="parent_name" class="form-control" placeholder="Ej: Postres" required>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-primary w-100">Crear</button>
                    </div>
                </form>
            </div>
        </div>

        @php
            $defaultFrom = $fromValue ?: now()->subDays(6)->toDateString();
            $defaultTo = $toValue ?: now()->toDateString();
        @endphp
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-2">Top vendidos (Clover)</h5>
                <p class="text-muted small mb-3">Selecciona un rango y consulta los items más vendidos.</p>
                <form method="GET" action="{{ route('admin.clover.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Desde</label>
                        <input type="date" name="from" class="form-control" value="{{ $defaultFrom }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="to" class="form-control" value="{{ $defaultTo }}">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button class="btn btn-outline-primary" type="submit" name="top_sellers" value="1">Ver top vendidos</button>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.clover.index') }}">Limpiar</a>
                    </div>
                </form>
                @if(!is_null($topSellers))
                    <div class="table-responsive mt-4">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Total ($)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topSellers as $row)
                                    <tr>
                                        <td>{{ $row['name'] }}</td>
                                        <td class="text-end">{{ number_format($row['quantity'], 2) }}</td>
                                        <td class="text-end">${{ number_format($row['revenue'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No hay ventas en ese rango.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('admin.clover.scopes.update') }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Clover ID</th>
                            <th>Orden</th>
                            <th>Borrada</th>
                            <th>Vista asignada</th>
                            <th>Categoría padre</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cloverCategories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td class="text-muted small">{{ $category->clover_id }}</td>
                                <td>{{ $category->sort_order }}</td>
                                <td>{{ $category->deleted ? 'Sí' : 'No' }}</td>
                                <td>
                                    <select class="form-select scope-select" name="scopes[{{ $category->id }}]">
                                        <option value="">Sin asignar</option>
                                        <option value="menu" @selected($category->scope === 'menu')>{{ $scopeLabels['menu'] }}</option>
                                        <option value="cocktails" @selected($category->scope === 'cocktails')>{{ $scopeLabels['cocktails'] }}</option>
                                        <option value="wines" @selected($category->scope === 'wines')>{{ $scopeLabels['wines'] }}</option>
                                        <option value="cantina" @selected($category->scope === 'cantina')>{{ $scopeLabels['cantina'] }}</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select parent-select"
                                            name="parent_categories[{{ $category->id }}]">
                                        <option value="">Usar categoría Clover</option>
                                        @foreach(($parentOptions['menu'] ?? []) as $parent)
                                            <option value="{{ $parent->id }}"
                                                    data-scope="menu"
                                                    @selected($category->parent_category_id === $parent->id)>
                                                {{ $parent->name }}
                                            </option>
                                        @endforeach
                                        @foreach(($parentOptions['cocktails'] ?? []) as $parent)
                                            <option value="{{ $parent->id }}"
                                                    data-scope="cocktails"
                                                    @selected($category->parent_category_id === $parent->id)>
                                                {{ $parent->name }}
                                            </option>
                                        @endforeach
                                        @foreach(($parentOptions['wines'] ?? []) as $parent)
                                            <option value="{{ $parent->id }}"
                                                    data-scope="wines"
                                                    @selected($category->parent_category_id === $parent->id)>
                                                {{ $parent->name }}
                                            </option>
                                        @endforeach
                                        @foreach(($parentOptions['cantina'] ?? []) as $parent)
                                            <option value="{{ $parent->id }}"
                                                    data-scope="cantina"
                                                    @selected($category->parent_category_id === $parent->id)>
                                                {{ $parent->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-muted small mt-1 mb-0">Si eliges un padre, esta categoría Clover se convertirá en subcategoría.</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No hay categorías Clover aún. Usa “Actualizar categorías”.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <a class="btn btn-outline-secondary" href="{{ route('admin.new-panel', ['section' => 'general']) }}">Volver a configuraciones</a>
                <button class="btn btn-primary">Guardar mapeo</button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const updateRow = (scopeSelect) => {
                const row = scopeSelect.closest('tr');
                if (!row) return;
                const parentSelect = row.querySelector('.parent-select');
                if (!parentSelect) return;
                const scope = scopeSelect.value;

                parentSelect.disabled = !scope;

                parentSelect.querySelectorAll('option[data-scope]').forEach((option) => {
                    option.hidden = scope && option.dataset.scope !== scope;
                });

                const selected = parentSelect.selectedOptions[0];
                if (selected?.dataset?.scope && selected.dataset.scope !== scope) {
                    parentSelect.value = '';
                }
            };

            document.querySelectorAll('.scope-select').forEach((scopeSelect) => {
                updateRow(scopeSelect);
                scopeSelect.addEventListener('change', () => updateRow(scopeSelect));
            });
        })();
    </script>
@endsection
