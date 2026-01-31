<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @php
        $settings = $settings ?? null;
        $seoTitle = 'Madeline Bistro · Ordena en línea';
        $seoDescription = 'Ordena en línea, paga seguro y recoge tu pedido en Madeline Bistro.';
        $seoImage = $settings?->logo
            ? asset('storage/' . $settings->logo)
            : asset('storage/default-logo.png');
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

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
        <header class="w-full px-6 py-6 flex items-center justify-between max-w-6xl mx-auto">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center overflow-hidden">
                    <img src="{{ $seoImage }}" alt="Logo" class="w-full h-full object-cover">
                </div>
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Orden en línea</p>
                    <h1 class="text-2xl font-semibold">Finaliza tu pedido</h1>
                </div>
            </div>
            <a href="{{ route('online.order.show') }}" class="text-sm text-slate-300 hover:text-white">
                Volver al menú
            </a>
        </header>

        <main class="flex-1 px-6 pb-12">
            <div class="max-w-5xl mx-auto grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="bg-white/5 border border-white/10 rounded-3xl p-6 lg:p-8">
                    <h2 class="text-lg font-semibold mb-2">Checkout seguro</h2>
                    <p class="text-sm text-slate-300 mb-6">
                        Completa el pago en la ventana segura de Clover. Al finalizar, te enviaremos el recibo y confirmaremos el pedido.
                    </p>
                    <div class="rounded-2xl border border-white/10 bg-black/30 p-6 text-sm text-slate-300">
                        Redirigiendo al checkout seguro de Clover...
                        <div class="mt-3">
                            <a class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-slate-100 hover:bg-white/20"
                               href="{{ $checkoutUrl }}" rel="noopener noreferrer">
                                Continuar al pago
                                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                            </a>
                        </div>
                        <p class="mt-3 text-xs text-slate-400">
                            Si no te redirige automáticamente, usa el botón de arriba.
                        </p>
                    </div>
                </div>

                <aside class="bg-white/5 border border-white/10 rounded-3xl p-6 lg:p-8">
                    <h3 class="text-lg font-semibold mb-4">Resumen de la orden</h3>
                    <div class="text-sm text-slate-300 space-y-2">
                        <div class="flex items-center justify-between">
                            <span>Orden #{{ $order->id }}</span>
                            <span class="uppercase tracking-[0.15em] text-xs text-slate-400">Pickup</span>
                        </div>
                        @if($order->customer_name)
                            <div class="flex items-center justify-between">
                                <span>Nombre</span>
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
                    @if($order->notes)
                        <div class="mt-4 text-sm text-slate-400">
                            <p class="font-semibold text-slate-200 mb-1">Notas</p>
                            <p>{{ $order->notes }}</p>
                        </div>
                    @endif
                    <div class="mt-6 p-4 rounded-2xl bg-white/5 text-sm text-slate-300">
                        <p class="font-semibold text-slate-100 mb-2">¿Necesitas ayuda?</p>
                        <p>Si tienes algún problema con el pago, vuelve al menú y crea una nueva orden.</p>
                    </div>
                </aside>
            </div>
        </main>
    </div>
    <script>
        // Hosted Checkout no se puede iframiar por CSP. Redirigimos a la URL segura.
        setTimeout(() => {
            window.location.href = @json($checkoutUrl);
        }, 600);
    </script>
</body>
</html>
