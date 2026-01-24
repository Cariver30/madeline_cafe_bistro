@php
    $reward = $redemption->reward;
    $customer = $redemption->customer;
    $customerName = $customer?->name ?? 'Invitado';
    $customerEmail = $customer?->email ?? '—';
    $customerPhone = $customer?->phone ?? '—';
    $expiresAt = $redemption->expires_at?->format('d M Y');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar recompensa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-white flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-2xl bg-white/5 border border-white/10 rounded-3xl p-8 space-y-6">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-amber-300 mb-2">Fidelidad</p>
            <h1 class="text-3xl font-semibold">Validar recompensa</h1>
            <p class="text-white/60 text-sm">Confirma la redención con el invitado antes de aprobar.</p>
        </div>

        @if(session('success'))
            <div class="bg-emerald-500/15 border border-emerald-400/40 text-emerald-100 rounded-2xl px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-500/15 border border-rose-400/40 text-rose-100 rounded-2xl px-4 py-3 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="bg-white/5 border border-white/10 rounded-2xl p-5 space-y-3">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-white/50">Cliente</p>
                <p class="text-lg font-semibold">{{ $customerName }}</p>
                <p class="text-white/60 text-sm">{{ $customerEmail }} · {{ $customerPhone }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-white/50">Recompensa</p>
                <p class="text-lg font-semibold">{{ $reward->title }}</p>
                @if($reward->description)
                    <p class="text-white/60 text-sm">{{ $reward->description }}</p>
                @endif
                <p class="text-white/60 text-sm">Puntos requeridos: {{ $reward->points_required }}</p>
                @if($expiresAt)
                    <p class="text-white/60 text-sm">Expira: {{ $expiresAt }}</p>
                @endif
            </div>
        </section>

        @if($state === 'approved')
            <div class="bg-emerald-500/10 border border-emerald-400/30 text-emerald-200 rounded-2xl px-4 py-3 text-sm">
                Esta recompensa ya fue validada.
            </div>
        @elseif($state === 'expired')
            <div class="bg-rose-500/10 border border-rose-400/30 text-rose-200 rounded-2xl px-4 py-3 text-sm">
                Este QR está expirado.
            </div>
        @elseif($state === 'rejected')
            <div class="bg-rose-500/10 border border-rose-400/30 text-rose-200 rounded-2xl px-4 py-3 text-sm">
                Esta redención fue rechazada.
            </div>
        @else
            <form method="POST" action="{{ route('loyalty.redeem.store', $redemption->qr_token) }}">
                @csrf
                <button type="submit" class="w-full rounded-full bg-amber-400 text-slate-900 font-semibold py-3">
                    Confirmar redención
                </button>
            </form>
        @endif
    </div>
</body>
</html>
