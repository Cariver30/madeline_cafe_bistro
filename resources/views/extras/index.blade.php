@extends('layouts.admin')

@section('title', 'Extras sugeridos')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-slate-900">Extras y add-ons</h1>
            <p class="text-slate-600 mt-1">Gestiona add-ons reutilizables para menú, café y cócteles.</p>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-400/30 bg-emerald-100/70 px-4 py-3 text-emerald-900 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @include('admin.partials.manage-extras', [
            'extras' => $extras,
            'redirectTo' => route('extras.index'),
        ])
    </div>
@endsection
