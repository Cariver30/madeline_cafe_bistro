@extends('layouts.admin')

@section('title', 'Registro de notificaciones')
@section('body-class', 'bg-slate-950 text-white font-sans antialiased')

@section('content')
<div class="space-y-6">
    <div class="glass-card flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-amber-400">Audiencia</p>
            <h1 class="text-3xl font-semibold text-white mt-1">Suscriptores a eventos</h1>
            <p class="text-sm text-slate-400">Lista generada desde la landing de experiencias para activar campañas en SendGrid.</p>
        </div>
        <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-2 px-5 py-2 rounded-full border border-white/10 text-sm text-slate-200 hover:bg-white/10 transition">
            ← Volver a eventos
        </a>
    </div>

    <div class="glass-card">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-slate-200">
                <thead>
                    <tr class="text-xs uppercase tracking-widest text-slate-500">
                        <th class="py-3 text-left">Nombre</th>
                        <th class="py-3 text-left">Email</th>
                        <th class="py-3 text-left">Evento</th>
                        <th class="py-3 text-left">Fecha registro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($notifications as $notification)
                        <tr>
                            <td class="py-3">{{ $notification->name }}</td>
                            <td class="py-3">{{ $notification->email }}</td>
                            <td class="py-3">{{ $notification->event?->title ?? 'General' }}</td>
                            <td class="py-3">{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-slate-500">Sin registros por ahora.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $notifications->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection
