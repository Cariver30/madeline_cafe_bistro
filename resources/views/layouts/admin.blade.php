<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin · '.config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @stack('styles')
</head>
<body class="@yield('body-class', 'bg-slate-100 text-slate-900 font-sans antialiased')">
    <div class="min-h-screen flex">
        <aside class="hidden lg:flex flex-col w-72 bg-white border-r border-slate-200 sticky top-0 shadow-sm">
            <div class="px-8 py-10 border-b border-slate-200">
                <p class="text-xs uppercase tracking-widest text-amber-500 mb-1">{{ config('app.name', 'Café Negro') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">Panel maestro</h1>
            </div>
            <nav class="flex-1 px-6 py-8 space-y-2">
                <a href="{{ route('admin.new-panel') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-transparent hover:border-slate-200 transition @if(request()->routeIs('admin.new-panel')) bg-amber-50 border-amber-200 @endif">
                    <span class="text-lg">🏠</span>
                    <div>
                        <p class="font-semibold text-sm text-slate-900">Panel general</p>
                        <p class="text-xs text-slate-500">Colores, menú, popups</p>
                    </div>
                </a>
                <a href="{{ route('admin.events.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-transparent hover:border-slate-200 transition @if(request()->routeIs('admin.events.*')) bg-amber-50 border-amber-200 @endif">
                    <span class="text-lg">🎟️</span>
                    <div>
                        <p class="font-semibold text-sm text-slate-900">Eventos especiales</p>
                        <p class="text-xs text-slate-500">Secciones y taquillas</p>
                    </div>
                </a>
                @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl bg-rose-600 text-white font-semibold shadow hover:bg-rose-500 transition">
                        <span class="text-lg">⏻</span>
                        <div class="text-left leading-tight">
                            <p class="text-sm">Cerrar sesión</p>
                            <p class="text-xs text-white/80">Salir del panel</p>
                        </div>
                    </button>
                </form>
                @endauth
            </nav>
            <div class="px-6 py-8 border-t border-slate-200 space-y-4">
                <a href="{{ route('cover') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm text-slate-700 rounded-full border border-slate-300 hover:bg-slate-100 transition">
                    <span>🔗</span> Ver portada
                </a>
                @auth
                <div class="text-xs text-slate-500 uppercase tracking-[0.35em]">Sesión activa</div>
                <div class="bg-slate-50 rounded-2xl p-3 text-sm text-slate-600 border border-slate-200">Has iniciado sesión como <strong>{{ auth()->user()->email }}</strong>.</div>
                @endauth
            </div>
        </aside>
        <main class="flex-1">
            <div class="px-4 py-6 sm:px-8 lg:px-12">
                <div class="lg:hidden mb-6">
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 space-y-2 shadow">
                        <p class="text-xs uppercase tracking-widest text-amber-500">Panel maestro</p>
                        <p class="text-lg font-semibold text-slate-900">Navega desde un equipo de escritorio para ver la barra lateral completa.</p>
                        <div class="flex flex-wrap gap-2 pt-2">
                            <a href="{{ route('admin.new-panel') }}" class="text-sm px-3 py-1.5 rounded-full border border-slate-300 hover:bg-slate-100 transition">Panel general</a>
                            <a href="{{ route('admin.events.index') }}" class="text-sm px-3 py-1.5 rounded-full border border-slate-300 hover:bg-slate-100 transition">Eventos</a>
                        </div>
                    </div>
                </div>
                <div class="max-w-6xl mx-auto">
                    @auth
                        <div class="flex justify-end mb-4 lg:hidden">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-amber-500/80 to-rose-500/80 text-sm font-semibold text-white">
                                    <span>⏻</span> Cerrar sesión
                                </button>
                            </form>
                        </div>
                    @endauth
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @stack('scripts')
</body>
</html>
