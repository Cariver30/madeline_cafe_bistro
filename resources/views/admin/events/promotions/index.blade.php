@extends('layouts.admin')

@section('title', 'Campañas promocionales')
@section('body-class', 'bg-slate-950 text-white font-sans antialiased')

@section('content')
<div class="space-y-6">
    <div class="glass-card flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-amber-400">Marketing</p>
            <h1 class="text-3xl font-semibold text-white mt-1">Campañas por SendGrid</h1>
            <p class="text-sm text-slate-400">Construye promos con PDF, GIF o videos y envíalas a la lista VIP.</p>
        </div>
        <a href="{{ route('admin.events.promotions.create') }}" class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-white/15 text-white font-semibold hover:bg-white/25 transition">
            ＋ Nueva campaña
        </a>
    </div>

    @if(session('success'))
        <div class="glass-card border border-emerald-400/30 text-emerald-200 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($promotions as $promotion)
            <article class="glass-card space-y-3">
                <p class="text-xs text-slate-400 uppercase tracking-[0.3em]">{{ $promotion->created_at->format('d M Y') }}</p>
                <h2 class="text-xl font-semibold text-white">{{ $promotion->title }}</h2>
                <p class="text-sm text-slate-400 line-clamp-3">{{ strip_tags($promotion->body_html) }}</p>
                <div class="flex items-center justify-between text-xs uppercase tracking-[0.4em]">
                    <span class="px-3 py-1 rounded-full {{ $promotion->status === 'sent' ? 'bg-emerald-500/10 text-emerald-300' : ($promotion->status === 'failed' ? 'bg-rose-500/10 text-rose-300' : 'bg-white/10 text-slate-300') }}">
                        {{ $promotion->status }}
                    </span>
                    <span class="text-slate-500">Enviados: {{ $promotion->send_count }}</span>
                </div>
                @if($promotion->send_error)
                    <p class="text-xs text-rose-300">{{ $promotion->send_error }}</p>
                @endif
            </article>
        @endforeach
    </div>

    <div>
        {{ $promotions->links('pagination::tailwind') }}
    </div>
</div>
@endsection
