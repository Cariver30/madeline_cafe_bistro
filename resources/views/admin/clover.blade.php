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
                    <label class="form-check text-muted small d-flex align-items-center gap-2 mb-0">
                        <input class="form-check-input" type="checkbox" name="sync_taxes" value="1">
                        Incluir taxes Clover (lento)
                    </label>
                </form>
                <form method="POST" action="{{ route('admin.clover.categories.sync') }}">
                    @csrf
                    <button class="btn btn-outline-secondary">Actualizar categorías</button>
                </form>
                <form method="POST" action="{{ route('admin.clover.items.sync') }}" class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
                    @csrf
                    <button class="btn btn-dark">Importar items</button>
                    <label class="form-check text-muted small d-flex align-items-center gap-2 mb-0">
                        <input class="form-check-input" type="checkbox" name="sync_taxes" value="1">
                        Incluir taxes Clover (lento)
                    </label>
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
                                    <select class="form-select" name="scopes[{{ $category->id }}]">
                                        <option value="">Sin asignar</option>
                                        <option value="menu" @selected($category->scope === 'menu')>Menú</option>
                                        <option value="cocktails" @selected($category->scope === 'cocktails')>Cócteles</option>
                                        <option value="wines" @selected($category->scope === 'wines')>Vinos / Café</option>
                                        <option value="cantina" @selected($category->scope === 'cantina')>Cantina</option>
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
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
@endsection
