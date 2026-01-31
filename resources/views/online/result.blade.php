<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @php
        $settings = $settings ?? null;
        $seoTitle = 'Madeline Bistro · Resultado de tu pedido';
        $seoDescription = 'Gracias por tu pedido en línea.';
        $seoImage = $settings?->logo
            ? asset('storage/' . $settings->logo)
            : asset('storage/default-logo.png');

        $status = $status ?? 'failure';
        $isSuccess = $status === 'success';
        $badgeClass = $isSuccess
            ? 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40'
            : 'bg-amber-500/20 text-amber-200 border-amber-400/40';
        $title = $isSuccess ? 'Pago confirmado' : 'Pago no completado';
        $message = $isSuccess
            ? 'Tu pedido fue confirmado y está en preparación.'
            : 'No pudimos completar el pago. Puedes intentarlo nuevamente desde el menú.';
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}" />
    <meta property="og:title" content="{{ $seoTitle }}" />
    <meta property="og:description" content="{{ $seoDescription }}" />
    <meta property="og:type" content="website" />
    <meta property="og:image" content="{{ $seoImage }}" />
    <meta property="og:site_name" content="Madeline Bistro" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $seoTitle }}" />
    <meta name="twitter:description" content="{{ $seoDescription }}" />
    <meta name="twitter:image" content="{{ $seoImage }}" />
    <link rel="icon" href="{{ $seoImage }}" />
    <link rel="apple-touch-icon" href="{{ $seoImage }}" />

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            min-height: 100vh;
            background: radial-gradient(circle at top, #0f172a, #020617);
            color: #e2e8f0;
        }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen flex flex-col">
        <header class="w-full px-6 py-6 flex items-center justify-between max-w-5xl mx-auto">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center overflow-hidden">
                    <img src="{{ $seoImage }}" alt="Logo" class="w-full h-full object-cover">
                </div>
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Orden en línea</p>
                    <h1 class="text-2xl font-semibold">Resultado del pago</h1>
                </div>
            </div>
            <a href="{{ route('online.order.show') }}" class="text-sm text-slate-300 hover:text-white">
                Volver al menú
            </a>
        </header>

        <main class="flex-1 px-6 pb-12">
            <div class="max-w-3xl mx-auto bg-white/5 border border-white/10 rounded-3xl p-6 lg:p-8 space-y-6">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h2 class="text-xl font-semibold">{{ $title }}</h2>
                        <p class="text-sm text-slate-300 mt-1">{{ $message }}</p>
                    </div>
                    <span class="text-xs uppercase tracking-[0.2em] border px-3 py-1 rounded-full {{ $badgeClass }}">
                        {{ $isSuccess ? 'Confirmado' : 'Pendiente' }}
                    </span>
                </div>

                <div class="bg-white/5 rounded-2xl p-4 text-sm text-slate-300">
                    <p class="font-semibold text-slate-100 mb-2">Detalle de la orden</p>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span>Orden</span>
                            <span>#{{ $order->id }}</span>
                        </div>
                        @if($order->customer_name)
                            <div class="flex items-center justify-between">
                                <span>Cliente</span>
                                <span>{{ $order->customer_name }}</span>
                            </div>
                        @endif
                        @if($order->pickup_at)
                            <div class="flex items-center justify-between">
                                <span>Hora de recogido</span>
                                <span>{{ $order->pickup_at->format('d M · H:i') }}</span>
                            </div>
                        @endif
                        @if($order->customer_email)
                            <div class="flex items-center justify-between">
                                <span>Email</span>
                                <span class="truncate max-w-[180px] text-right">{{ $order->customer_email }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if($isSuccess && $order->customer_email)
                    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-4 text-sm">
                        Enviamos el recibo al correo registrado. Si no lo ves, revisa el spam.
                    </div>
                @endif

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('online.order.show') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-full bg-amber-400 text-slate-900 font-semibold">
                        Crear otra orden
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
