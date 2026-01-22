@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.admin')

@section('title', 'Eventos especiales · Panel')
@section('body-class', 'bg-slate-950 text-white font-sans antialiased')

@section('content')
<div class="space-y-10">
    <section class="bg-gradient-to-br from-blue-900 via-indigo-800 to-blue-700 rounded-3xl p-8 shadow-2xl border border-white/10 text-white overflow-hidden relative">
        <div class="absolute inset-y-0 right-0 opacity-25 pointer-events-none hidden md:block">
            <div class="w-72 h-72 rounded-full bg-blue-500 blur-3xl"></div>
        </div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="uppercase tracking-[0.35em] text-xs text-white/65 mb-3">Experiencias</p>
                <h1 class="text-3xl md:text-4xl font-semibold mb-3">Eventos especiales y reservas</h1>
                <p class="text-base text-white/80 max-w-2xl">Centraliza la creación de eventos, sus secciones, mapas y ventas desde un mismo entorno con estética premium.</p>
            </div>
            <a href="{{ route('admin.events.create') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-white/90 text-blue-900 font-semibold shadow-lg hover:bg-white">
                <span class="text-lg">＋</span> Nuevo evento
            </a>
        </div>
    </section>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <section class="grid md:grid-cols-3 gap-5">
        <article class="glass-tile">
            <div class="text-xs uppercase tracking-widest text-slate-400 mb-3">Eventos creados</div>
            <div class="flex items-baseline gap-3">
                <p class="text-4xl font-semibold text-white">{{ $stats['total'] ?? 0 }}</p>
                <span class="px-3 py-1 rounded-full bg-blue-500/10 text-blue-300 text-xs">Total</span>
            </div>
        </article>
        <article class="glass-tile">
            <div class="text-xs uppercase tracking-widest text-slate-400 mb-3">Eventos activos</div>
            <div class="flex items-baseline gap-3">
                <p class="text-4xl font-semibold text-white">{{ $stats['active'] ?? 0 }}</p>
                <span class="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-300 text-xs">Publicados</span>
            </div>
        </article>
        <article class="glass-tile">
            <div class="text-xs uppercase tracking-widest text-slate-400 mb-3">Eventos próximos</div>
            <div class="flex items-baseline gap-3">
                <p class="text-4xl font-semibold text-white">{{ $stats['upcoming'] ?? 0 }}</p>
                <span class="px-3 py-1 rounded-full bg-amber-500/10 text-amber-300 text-xs">Calendario</span>
            </div>
        </article>
    </section>

    <section class="grid xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <article class="glass-tile">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-white">Eventos registrados</h2>
                        <p class="text-sm text-slate-400">Filtra por nombre o estado para ubicar eventos rápido.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <div class="relative">
                            <input id="eventSearch" type="search" placeholder="Buscar evento..." class="input-field pl-11 pr-4 py-2.5 w-60">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">⌕</span>
                        </div>
                        <select id="statusFilter" class="input-field py-2.5 w-40">
                            <option value="">Todos</option>
                            <option value="activo">Activos</option>
                            <option value="borrador">Borradores</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-slate-300" id="eventsTable">
                        <thead>
                            <tr class="text-xs uppercase tracking-widest text-slate-500">
                                <th class="py-3 text-left">Evento</th>
                                <th class="py-3 text-left">Fechas</th>
                                <th class="py-3 text-left">Estado</th>
                                <th class="py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($events as $event)
                                <tr class="hover:bg-white/5 transition" data-title="{{ Str::lower($event->title) }}" data-status="{{ $event->is_active ? 'activo' : 'borrador' }}">
                                    <td class="py-4">
                                        <p class="font-semibold text-white">{{ $event->title }}</p>
                                        <p class="text-xs text-slate-500">{{ $event->slug }}</p>
                                    </td>
                                    <td class="py-4">
                                        <p class="text-xs text-slate-400">Inicio · {{ $event->start_at->format('d/m/Y H:i') }}</p>
                                        @if($event->end_at)
                                            <p class="text-xs text-slate-400">Fin · {{ $event->end_at->format('d/m/Y H:i') }}</p>
                                        @endif
                                    </td>
                                    <td class="py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $event->is_active ? 'bg-emerald-500/10 text-emerald-300' : 'bg-slate-700 text-slate-300' }}">
                                            {{ $event->is_active ? 'Activo' : 'Borrador' }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-right">
                                        <a href="{{ route('admin.events.edit', $event) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/10 hover:bg-white/10 transition text-sm">
                                            <span>✏️</span> Gestionar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-10 text-center text-slate-500 text-sm">No hay registros aún.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $events->links('pagination::tailwind') }}
                </div>
            </article>
        </div>
        <div class="space-y-6">
            <article class="glass-tile">
                <h3 class="text-lg font-semibold text-white mb-4">Atajo rápido</h3>
                <p class="text-sm text-slate-400 mb-5">Regresa al panel principal para seguir ajustando menú, colores y pop-ups.</p>
                <a href="{{ route('admin.new-panel') }}" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/15 transition text-sm font-semibold">
                    <span>↺</span> Ir al panel general
                </a>
            </article>
            <article class="glass-tile">
                <h3 class="text-lg font-semibold text-white mb-4">Acciones recomendadas</h3>
                <div class="space-y-3 text-sm">
                    <a href="{{ route('admin.events.promotions.index') }}" class="recommendation-link">✉️ Crear campaña masiva</a>
                    <a href="{{ route('admin.events.notifications') }}" class="recommendation-link">🗒️ Ver registros de notificaciones</a>
                    <a href="{{ route('settings.edit') }}" class="recommendation-link">🎨 Personalizar colores y fuentes</a>
                    <a href="{{ route('admin.popups.index') }}" class="recommendation-link">📣 Programar pop-up para evento</a>
                    <a href="{{ route('wine-categories.index') }}" class="recommendation-link">🍷 Definir maridajes sugeridos</a>
                </div>
            </article>
            <article class="glass-tile">
                <h3 class="text-lg font-semibold text-white mb-4">Últimas taquillas</h3>
                <ul class="space-y-4">
                    @forelse($latestTickets as $ticket)
                        <li class="text-sm text-slate-300">
                            <p class="font-semibold text-white">{{ $ticket->customer_name }}</p>
                            <p class="text-slate-400">{{ $ticket->section?->name }} · {{ $ticket->guest_count }} invitados</p>
                            <p class="text-slate-500">${{ number_format($ticket->total_paid, 2) }}</p>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Aún no hay ventas registradas.</li>
                    @endforelse
                </ul>
            </article>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .glass-tile {
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 1.5rem;
        padding: 1.5rem;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.35);
        backdrop-filter: blur(18px);
    }
    .input-field {
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 1.25rem;
        font-size: 0.875rem;
        color: #e2e8f0;
        padding-left: 1rem;
        padding-right: 1rem;
    }
    .input-field::placeholder {
        color: #64748b;
    }
    .input-field:focus {
        outline: 2px solid rgba(96, 165, 250, 0.6);
        border-color: rgba(96, 165, 250, 0.6);
    }
    .recommendation-link {
        display: block;
        padding: 0.85rem 1rem;
        border-radius: 1.25rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #cbd5f5;
        text-decoration: none;
        transition: background-color .2s ease;
    }
    .recommendation-link:hover {
        background-color: rgba(255, 255, 255, 0.08);
    }
</style>
@endpush

@push('scripts')
<script>
    const searchInput = document.getElementById('eventSearch');
    const statusFilter = document.getElementById('statusFilter');
    const rows = document.querySelectorAll('#eventsTable tbody tr');

    function applyFilters() {
        const query = (searchInput?.value || '').toLowerCase();
        const status = statusFilter?.value;

        rows.forEach(row => {
            const title = (row.dataset.title || '').toLowerCase();
            const matchesTitle = title.includes(query);
            const matchesStatus = status ? row.dataset.status === status : true;
            row.style.display = matchesTitle && matchesStatus ? '' : 'none';
        });
    }

    searchInput?.addEventListener('input', applyFilters);
    statusFilter?.addEventListener('change', applyFilters);
</script>
@endpush
