@extends('layouts.admin')

@section('title', 'Especiales y ofertas')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Especiales y ofertas</h1>
            <p class="text-sm text-slate-500">Crea especiales por día y hora con items de las vistas activas.</p>
        </div>
        <a href="{{ route('admin.specials.create') }}" class="primary-button inline-flex w-auto px-6">Crear especial</a>
    </div>

    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 text-emerald-800 px-4 py-3 text-sm border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if($specials->count())
        <div class="grid gap-4 md:grid-cols-2">
            @foreach($specials as $special)
                <div class="module-card space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900">{{ $special->name }}</h2>
                        <span class="text-xs px-2 py-1 rounded-full {{ $special->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $special->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <div class="text-sm text-slate-600">
                        <span class="font-semibold">{{ $special->categories_count }}</span> categorías ·
                        <span class="font-semibold">{{ $special->items_count }}</span> items
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                        <span>Horas: {{ $special->starts_at ?? '—' }} - {{ $special->ends_at ?? '—' }}</span>
                        <span>Días: {{ $special->days_of_week ? implode(', ', $special->days_of_week) : 'Todos' }}</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.specials.edit', $special) }}" class="ghost-button w-auto px-4">Editar</a>
                        <form method="POST" action="{{ route('admin.specials.destroy', $special) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ghost-button w-auto px-4 border-rose-200 text-rose-600">Eliminar</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div>
            {{ $specials->links('pagination::tailwind') }}
        </div>
    @else
        <div class="module-card text-center text-slate-600">
            No hay especiales creados aún.
        </div>
    @endif
</div>
@endsection
