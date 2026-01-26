@extends('layouts.admin')

@section('title', 'Panel maestro · ajustes globales')

@section('content')
@php
    $currentUser = auth()->user();
    $tabLabels = [
        'menu' => $settings->tab_label_menu ?? $settings->button_label_menu ?? 'Menú',
        'cocktails' => $settings->tab_label_cocktails ?? $settings->button_label_cocktails ?? 'Cócteles',
        'wines' => $settings->tab_label_wines ?? $settings->button_label_wines ?? 'Café & Brunch',
        'cantina' => $settings->tab_label_cantina ?? $settings->button_label_cantina ?? 'Cantina',
        'events' => $settings->tab_label_events ?? 'Eventos',
        'loyalty' => $settings->tab_label_loyalty ?? 'Fidelidad',
    ];
    $tabLabelsSingular = [
        'cocktails' => \Illuminate\Support\Str::singular($tabLabels['cocktails']) ?: $tabLabels['cocktails'],
        'cantina' => \Illuminate\Support\Str::singular($tabLabels['cantina']) ?: $tabLabels['cantina'],
    ];
@endphp
<div class="space-y-10">
    <section class="bg-white rounded-3xl p-8 border border-slate-200 shadow-xl relative overflow-hidden">
        <div class="absolute inset-y-0 -right-10 opacity-20 pointer-events-none hidden md:block">
            <div class="w-64 h-64 rounded-full bg-amber-200 blur-3xl"></div>
        </div>
        <div class="relative z-10">
            <p class="uppercase tracking-[0.3em] text-xs text-amber-500 mb-3">Operación diaria</p>
            <h1 class="text-3xl md:text-4xl font-semibold text-slate-900 mb-3">Panel operativo</h1>
            <p class="text-slate-600 max-w-2xl">
                Ventas, mesas activas, propinas y desempeño del día. El resto de configuraciones vive en secciones dedicadas.
            </p>
        </div>
    </section>

    <section class="glass-card">
        <div class="tab-groups" id="adminTabs">
            <div class="tab-group">
                <p class="tab-group-title">Dashboard</p>
                <div class="tab-group-buttons">
                    <button class="tab-button active" data-section="dashboard">Operación del día</button>
                </div>
            </div>

            <div class="tab-group">
                <p class="tab-group-title">Vistas al cliente</p>
                <div class="tab-group-buttons">
                    @if($settings->show_tab_menu)
                        <button class="tab-button" data-section="menu">{{ $tabLabels['menu'] }}</button>
                    @endif
                    @if($settings->show_tab_cocktails)
                        <button class="tab-button" data-section="cocktails">{{ $tabLabels['cocktails'] }}</button>
                    @endif
                    @if($settings->show_tab_wines)
                        <button class="tab-button" data-section="wines">{{ $tabLabels['wines'] }}</button>
                    @endif
                    @if($settings->show_tab_cantina)
                        <button class="tab-button" data-section="cantina">{{ $tabLabels['cantina'] }}</button>
                    @endif
                    <button class="tab-button" data-section="specials">Especiales</button>
                    <button class="tab-button" data-section="featured">Lo más vendido</button>
                </div>
            </div>

            <div class="tab-group">
                <p class="tab-group-title">POS</p>
                <div class="tab-group-buttons">
                    <button class="tab-button" data-section="extras">Extras</button>
                    <button class="tab-button" data-section="taxes">Impuestos</button>
                    <button class="tab-button" data-section="printers">Impresoras</button>
                    <button class="tab-button" data-section="prep">Preparación</button>
                    @if($settings->show_tab_loyalty)
                        <button class="tab-button" data-section="loyalty-section">{{ $tabLabels['loyalty'] }}</button>
                    @endif
                </div>
            </div>

            <div class="tab-group">
                <p class="tab-group-title">Marketing</p>
                <div class="tab-group-buttons">
                    @if($settings->show_tab_events)
                        <button class="tab-button" data-section="events">{{ $tabLabels['events'] }}</button>
                    @endif
                    @if($settings->show_tab_campaigns)
                        <button class="tab-button" data-section="campaigns">Campañas</button>
                    @endif
                    @if($settings->show_tab_popups)
                        <button class="tab-button" data-section="popups">Pop-ups</button>
                    @endif
                </div>
            </div>

            @if($currentUser?->isAdmin())
                <div class="tab-group">
                    <p class="tab-group-title">Sistema</p>
                    <div class="tab-group-buttons">
                        <button class="tab-button" data-section="general">Configuraciones</button>
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-8">
            <div id="dashboard" class="section-panel active">
                <div class="inner-panel space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="inner-title mb-1">Operación del día</h3>
                            <p class="inner-text m-0">Ventas, propinas, mesas activas y rendimiento.</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">
                            {{ \Carbon\Carbon::now()->format('d M, Y') }}
                        </span>
                    </div>

                    <div class="grid md:grid-cols-4 gap-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-xs uppercase tracking-widest text-slate-500">Ventas</p>
                            <p class="text-2xl font-semibold text-slate-900">${{ number_format($opsTotals['sales_total'] ?? 0, 2) }}</p>
                            <p class="text-xs text-slate-500">
                                @if(!is_null($opsTotals['sales_delta_percent'] ?? null))
                                    {{ ($opsTotals['sales_delta_percent'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($opsTotals['sales_delta_percent'] ?? 0, 1) }}% vs ayer
                                @else
                                    Sin comparación
                                @endif
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-xs uppercase tracking-widest text-slate-500">Propinas</p>
                            <p class="text-2xl font-semibold text-slate-900">${{ number_format($opsTotals['tips_total'] ?? 0, 2) }}</p>
                            <p class="text-xs text-slate-500">{{ $opsTotals['orders_count'] ?? 0 }} órdenes confirmadas</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-xs uppercase tracking-widest text-slate-500">Mesas activas</p>
                            <p class="text-2xl font-semibold text-slate-900">{{ $opsTotals['open_tables'] ?? 0 }}</p>
                            <p class="text-xs text-slate-500">Tickets abiertos: {{ $opsTotals['open_tickets'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-xs uppercase tracking-widest text-slate-500">Anulados</p>
                            <p class="text-2xl font-semibold text-slate-900">${{ number_format($opsTotals['voided_total'] ?? 0, 2) }}</p>
                            <p class="text-xs text-slate-500">Pérdidas del día</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-xs uppercase tracking-widest text-slate-500">Ventas semana</p>
                            <p class="text-2xl font-semibold text-slate-900">${{ number_format($opsTotals['sales_week_total'] ?? 0, 2) }}</p>
                            <p class="text-xs text-slate-500">
                                @if(!is_null($opsTotals['sales_week_delta_percent'] ?? null))
                                    {{ ($opsTotals['sales_week_delta_percent'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($opsTotals['sales_week_delta_percent'] ?? 0, 1) }}% vs semana pasada
                                @else
                                    Sin comparación
                                @endif
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-xs uppercase tracking-widest text-slate-500">Semana pasada</p>
                            <p class="text-2xl font-semibold text-slate-900">${{ number_format($opsTotals['sales_week_prev'] ?? 0, 2) }}</p>
                            <p class="text-xs text-slate-500">Comparativo semanal</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-xs uppercase tracking-widest text-slate-500">Tickets abiertos</p>
                            <p class="text-2xl font-semibold text-slate-900">{{ $opsTotals['open_tickets'] ?? 0 }}</p>
                            <p class="text-xs text-slate-500">Walk-in + teléfono</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-semibold text-slate-900">Ventas por canal</h4>
                            </div>
                            <div class="space-y-3">
                                @forelse($opsSalesByChannel ?? [] as $channel)
                                    <div class="flex items-center justify-between text-sm">
                                        <div>
                                            <p class="font-semibold text-slate-900">
                                                {{ $channel['channel'] === 'walkin' ? 'Walk-in' : ($channel['channel'] === 'phone' ? 'Teléfono' : 'Mesa') }}
                                            </p>
                                            <p class="text-xs text-slate-500">{{ $channel['orders_count'] }} órdenes</p>
                                        </div>
                                        <span class="font-semibold text-slate-900">${{ number_format($channel['sales_total'], 2) }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">Sin datos por canal.</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-semibold text-slate-900">Top productos del día</h4>
                            </div>
                            <div class="space-y-3">
                                @forelse($opsTopItems ?? [] as $item)
                                    <div class="flex items-center justify-between text-sm">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $item['name'] }}</p>
                                            <p class="text-xs text-slate-500">{{ $item['quantity'] }} vendidos</p>
                                        </div>
                                        <span class="font-semibold text-slate-900">${{ number_format($item['revenue'], 2) }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">Sin productos destacados.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <div>
                                <h4 class="text-base font-semibold text-slate-900">Meseros en turno</h4>
                                <p class="text-xs text-slate-500">Actividad en tiempo real.</p>
                            </div>
                            <span class="text-xs text-slate-500">
                                {{ collect($opsServers ?? [])->where('is_online', true)->count() }} en turno
                            </span>
                        </div>
                        <div class="space-y-3">
                            @forelse($opsServers ?? [] as $server)
                                <div class="flex flex-wrap items-center justify-between gap-4 border border-slate-100 rounded-xl px-4 py-3">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $server['name'] }}</p>
                                        <p class="text-xs text-slate-500">{{ $server['email'] }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $server['is_online'] ? 'En turno' : 'Fuera de turno' }} · {{ $server['active'] ? 'Activo' : 'Pausado' }}
                                        </p>
                                    </div>
                                    <div class="text-xs text-slate-600 space-y-1 text-right">
                                        <div>Mesas: <span class="font-semibold text-slate-900">{{ $server['active_tables'] }}</span></div>
                                        <div>Pendientes: <span class="font-semibold text-slate-900">{{ $server['open_orders'] }}</span></div>
                                        <div>Ventas: <span class="font-semibold text-slate-900">${{ number_format($server['sales_total'], 2) }}</span></div>
                                        <div>Propinas: <span class="font-semibold text-slate-900">${{ number_format($server['tips_total'], 2) }}</span></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">No hay meseros registrados.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            @if($currentUser && $currentUser->hasRole(['admin', 'manager']))
                <div id="general" class="section-panel">
                    <div class="inner-panel">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                            <div>
                                <h3 class="inner-title mb-1">Configuración general</h3>
                                <p class="inner-text m-0">Logo, redes, info fija y estilo del cover.</p>
                            </div>
                            <button type="button" class="btn btn-outline-light btn-sm" data-section="dashboard">Cerrar</button>
                        </div>
                        @include('admin.partials.general-config')
                    </div>
                </div>
            @endif

            <div id="menu" class="section-panel">
                <div class="inner-panel space-y-4">
                    <h3 class="inner-title">{{ $tabLabels['menu'] }}</h3>
            <div class="subnav">
                <button class="subnav-button active" data-target="menu-create">Crear nuevo plato</button>
                <button class="subnav-button" data-target="menu-config">Configuración de Menú</button>
                <button class="subnav-button" data-target="menu-promos">Campañas</button>
            </div>
            <div id="menu-create" class="subnav-panel show">
                @include('admin.partials.manage-dishes')
            </div>
            <div id="menu-config" class="subnav-panel">
                @include('admin.partials.menu-config')
            </div>
            <div id="menu-promos" class="subnav-panel">
                <p class="text-sm text-slate-600 mb-3">Gestión rápida de promociones.</p>
                <a href="{{ route('admin.events.promotions.index') }}" class="primary-button inline-flex justify-center">Ver campañas</a>
            </div>
        </div>
            </div>

            <div id="cocktails" class="section-panel">
                <div class="inner-panel space-y-4">
                    <h3 class="inner-title">{{ $tabLabels['cocktails'] }}</h3>
                    <div class="subnav">
                        <button class="subnav-button active" data-target="cocktail-create">Crear {{ \Illuminate\Support\Str::lower($tabLabelsSingular['cocktails']) }}</button>
                        <button class="subnav-button" data-target="cocktail-config">Configuración de {{ $tabLabels['cocktails'] }}</button>
                    </div>
                    <div id="cocktail-create" class="subnav-panel show">
                        @include('admin.partials.manage-cocktails', ['cocktailLabel' => $tabLabels['cocktails']])
                    </div>
                    <div id="cocktail-config" class="subnav-panel">
                        @include('admin.partials.cocktails-config', ['cocktailLabel' => $tabLabels['cocktails']])
                    </div>
                </div>
            </div>

            <div id="cantina" class="section-panel">
                <div class="inner-panel space-y-4">
                    <h3 class="inner-title">{{ $tabLabels['cantina'] }}</h3>
                    <div class="subnav">
                        <button class="subnav-button active" data-target="cantina-create">Crear {{ \Illuminate\Support\Str::lower($tabLabelsSingular['cantina']) }}</button>
                        <button class="subnav-button" data-target="cantina-config">Configuración de {{ $tabLabels['cantina'] }}</button>
                    </div>
                    <div id="cantina-create" class="subnav-panel show">
                        @include('admin.partials.manage-cantina', ['cantinaLabel' => $tabLabels['cantina']])
                    </div>
                    <div id="cantina-config" class="subnav-panel">
                        @include('admin.partials.cantina-config', ['cantinaLabel' => $tabLabels['cantina']])
                    </div>
                </div>
            </div>

            <div id="featured" class="section-panel">
                @include('admin.partials.featured-tabs')
            </div>

            <div id="extras" class="section-panel">
                <div class="inner-panel space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="inner-title">Extras y add-ons</h3>
                        <p class="inner-text mb-0">Agrega toppings, shots o complementos reutilizables.</p>
                    </div>
                    @include('admin.partials.manage-extras', ['extras' => $extras, 'redirectTo' => route('admin.new-panel', ['section' => 'extras'])])
                </div>
            </div>

            <div id="taxes" class="section-panel">
                <div class="inner-panel space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="inner-title">Impuestos</h3>
                        <p class="inner-text mb-0">Crea y administra los taxes aplicables a productos y categorías.</p>
                    </div>
                    @include('admin.partials.taxes-config')
                </div>
            </div>

            <div id="printers" class="section-panel">
                <div class="inner-panel space-y-4">
                    <h3 class="inner-title">Impresoras CloudPRNT</h3>
                    @include('admin.partials.printers-config')
                </div>
            </div>

            <div id="prep" class="section-panel">
                <div class="inner-panel space-y-4">
                    <h3 class="inner-title">Áreas y labels de preparación</h3>
                    @include('admin.partials.prep-config')
                </div>
            </div>

            <div id="wines" class="section-panel">
                <div class="inner-panel space-y-4">
                    <h3 class="inner-title">{{ $tabLabels['wines'] }}</h3>
                    <div class="subnav">
                        <button class="subnav-button active" data-target="wine-create">Crear bebida</button>
                        <button class="subnav-button" data-target="wine-config">Configuración de Café</button>
                        <button class="subnav-button" data-target="wine-advanced">Gestión avanzada</button>
                    </div>
                    <div id="wine-create" class="subnav-panel show">
                        @include('admin.partials.manage-wines')
                    </div>
                    <div id="wine-config" class="subnav-panel">
                        @include('admin.partials.wines-config')
                    </div>
                    <div id="wine-advanced" class="subnav-panel">
                        @include('admin.partials.wine-advanced')
                    </div>
                </div>
            </div>

            <div id="events" class="section-panel">
                <div class="inner-panel">
                    <h3 class="inner-title">{{ $tabLabels['events'] }}</h3>
                    <p class="inner-text">Configura eventos, mapa de secciones y taquillas.</p>
                    <a href="{{ route('admin.events.index') }}" class="primary-button mt-4 inline-flex">Ir a gestor de eventos</a>
                </div>
            </div>

            <div id="campaigns" class="section-panel">
                <div class="inner-panel space-y-4">
                    <h3 class="inner-title">Campañas promocionales</h3>
                    <p class="inner-text">Envía boletines, cupones o lanzamientos a toda la lista de notificaciones usando la API de SendGrid.</p>
                    <ul class="text-sm text-slate-600 space-y-2">
                        <li>• Arrastra PDF/GIF/videos para adjuntarlos.</li>
                        <li>• Redacta el HTML en un editor simple.</li>
                        <li>• Envía en bulk o guarda como borrador.</li>
                    </ul>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.events.promotions.index') }}" class="primary-button inline-flex justify-center">Ver campañas</a>
                        <a href="{{ route('admin.events.promotions.create') }}" class="ghost-button inline-flex justify-center">Crear nueva</a>
                    </div>
                </div>
            </div>

            <div id="specials" class="section-panel">
                <div class="inner-panel space-y-4">
                    <h3 class="inner-title">Especiales y ofertas</h3>
                    <div class="subnav">
                        <button class="subnav-button active" data-target="specials-manage">Gestionar especiales</button>
                        <button class="subnav-button" data-target="specials-config">Configuración de Especiales</button>
                    </div>
                    <div id="specials-manage" class="subnav-panel show">
                        <p class="inner-text">Crea especiales por día y hora, seleccionando categorías e items visibles de las vistas activas.</p>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('admin.specials.index') }}" class="primary-button inline-flex justify-center">Ver especiales</a>
                            <a href="{{ route('admin.specials.create') }}" class="ghost-button inline-flex justify-center">Crear especial</a>
                        </div>
                    </div>
                    <div id="specials-config" class="subnav-panel">
                        @include('admin.partials.specials-config')
                    </div>
                </div>
            </div>

            <div id="popups" class="section-panel">
                <div class="inner-panel">
                    <h3 class="inner-title">Pop-ups</h3>
                    @include('admin.partials.manage-popups')
                </div>
            </div>

            <div id="loyalty-section" class="section-panel">
                @include('admin.partials.loyalty')
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .glass-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1.5rem;
        padding: 1.75rem;
        box-shadow: 0 15px 30px rgba(15, 23, 42, 0.08);
    }
    .tab-groups {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .tab-group {
        padding-bottom: 1rem;
        border-bottom: 1px dashed #e2e8f0;
    }
    .tab-group:last-of-type {
        border-bottom: none;
        padding-bottom: 0;
    }
    .tab-group-title {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.2em;
        color: #94a3b8;
        margin-bottom: 0.6rem;
        font-weight: 600;
    }
    .tab-group-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }
    .glass-grid {
        display: grid;
        gap: 1.25rem;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }
    .tab-button {
        padding: 0.6rem 1.5rem;
        border-radius: 9999px;
        border: 1px solid transparent;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all .2s;
    }
    .tab-button.active {
        background: #fcd34d;
        border-color: #fbbf24;
        color: #7c2d12;
    }
    .section-panel {
        display: none;
    }
    .section-panel.active {
        display: block;
    }
    .module-card {
        background: #ffffff;
        border-radius: 1.25rem;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(15,23,42,0.08);
    }
    .ghost-button {
        display: inline-flex;
        justify-content: center;
        width: 100%;
        padding: 0.75rem;
        border-radius: 9999px;
        border: 1px solid #cbd5f5;
        color: #0f172a;
    }
    .primary-button {
        display: inline-flex;
        justify-content: center;
        width: 100%;
        padding: 0.75rem;
        border-radius: 9999px;
        background: linear-gradient(120deg,#fcd34d,#f97316);
        color: #111827;
        font-weight: 600;
    }
    .inner-panel {
        background: #ffffff;
        border-radius: 1.5rem;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(15,23,42,0.05);
    }
    .feature-card {
        background: #ffffff;
        border-radius: 1.25rem;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 18px rgba(15,23,42,0.05);
    }
    .inner-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #0f172a;
    }
    .inner-text {
        font-size: 0.9rem;
        color: #475569;
        margin-bottom: 1.25rem;
    }
    .menu-config label,
    .cocktails-config label,
    .wines-config label,
    .cantina-config label,
    .specials-config label {
        color: #0f172a !important;
    }
    .menu-config p,
    .cocktails-config p,
    .wines-config p,
    .cantina-config p,
    .specials-config p {
        color: #475569;
    }
    .subnav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .subnav-button {
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        font-size: 0.8rem;
    }
    .subnav-button.active {
        background: #fde68a;
        border-color: #f59e0b;
        color: #7c2d12;
    }
    .subnav-panel {
        display: none;
        border-radius: 1rem;
        padding: 1.25rem;
        background: #ffffff;
        border: 1px solid #e2e8f0;
    }
    .subnav-panel.show {
        display: block;
    }
    /* Bootstrap form compatibility */
    .form-control,
    .form-select {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        color: #0f172a;
        border-radius: 0.85rem;
    }
    .form-control:focus,
    .form-select:focus {
        border-color: rgba(251, 191, 36, 0.8);
        box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.15);
        background-color: #ffffff;
    }
    .btn {
        border-radius: 9999px;
        padding: 0.5rem 1.5rem;
        border: none;
    }
    .btn-primary {
        background: linear-gradient(90deg, #fbbf24, #f97316);
        color: #111827;
        font-weight: 600;
    }
    .btn-outline-light {
        border: 1px solid #cbd5f5;
        color: #0f172a;
    }
    .card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        color: #0f172a;
        padding: 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
    const tabs = document.querySelectorAll('#adminTabs .tab-button');
    const panels = document.querySelectorAll('.section-panel');

    function openSection(target) {
        if (!target || !document.getElementById(target)) return;
        tabs.forEach(t => t.classList.remove('active'));
        const nav = document.querySelector(`#adminTabs .tab-button[data-section="${target}"]`);
        nav?.classList.add('active');
        panels.forEach(panel => panel.classList.remove('active'));
        document.getElementById(target).classList.add('active');
        window.history.replaceState({}, '', `?section=${target}`);
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => openSection(tab.dataset.section));
    });

    document.querySelectorAll('[data-section]').forEach(trigger => {
        if (trigger.closest('#adminTabs')) return;
        trigger.addEventListener('click', () => openSection(trigger.dataset.section));
    });

    window.toggleVisibility = function(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.classList.toggle('hidden');
        }
    };

    document.querySelectorAll('.subnav-button').forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.target;
            const container = button.closest('.inner-panel');
            container.querySelectorAll('.subnav-button').forEach(b => b.classList.remove('active'));
            container.querySelectorAll('.subnav-panel').forEach(panel => panel.classList.remove('show'));
            button.classList.add('active');
            container.querySelector(`#${targetId}`).classList.add('show');
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        const section = params.get('section');
        if (section && document.getElementById(section)) {
            openSection(section);
        }

        const focus = params.get('focus');
        if (focus) {
            setTimeout(() => {
                const anchor = document.getElementById(focus);
                if (anchor) {
                    anchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    anchor.classList.add('shadow-lg');
                    anchor.style.boxShadow = '0 0 0 3px rgba(251,191,36,0.45)';
                    setTimeout(() => {
                        anchor.classList.remove('shadow-lg');
                        anchor.style.boxShadow = '';
                    }, 1800);
                }
            }, 250);
        }
    });
</script>
@endpush
