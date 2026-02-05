<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías · Cantina</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" />
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <div class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-0 space-y-8">
        <a href="{{ route('admin.new-panel', ['section' => 'cantina']) }}" class="inline-flex items-center gap-2 text-sm text-white/70 hover:text-white transition">
            <span class="text-lg">←</span> Volver al panel
        </a>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-white/60">Cantina</p>
                <h1 class="text-3xl font-semibold">Categorías</h1>
                <p class="text-white/60 text-sm">Organiza y publica las categorías de la cantina.</p>
            </div>
            <a href="{{ route('cantina-categories.create') }}" class="rounded-2xl bg-amber-400 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-300">
                + Nueva categoría
            </a>
        </div>

        @if ($message = Session::get('success'))
            <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-100">
                {{ $message }}
            </div>
        @endif

        <div class="rounded-3xl border border-white/10 bg-white/5 backdrop-blur shadow-2xl overflow-hidden">
            <div class="divide-y divide-white/10">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 px-6 py-4 text-xs uppercase tracking-[0.3em] text-white/50">
                    <div>Nombre</div>
                    <div>Portada</div>
                    <div class="text-right">Acciones</div>
                </div>
                @forelse ($categories as $category)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 px-6 py-4 items-center">
                        <div>
                            <p class="text-lg font-semibold">{{ $category->name }}</p>
                            <p class="text-sm text-white/60">{{ $category->cover_title ?: 'Sin nombre público' }}</p>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/10 px-3 py-1 text-xs text-white/70">
                                {{ $category->show_on_cover ? 'Visible en portada' : 'Oculta en portada' }}
                            </span>
                        </div>
                        <div class="flex flex-wrap justify-end gap-2">
                            <a href="{{ route('cantina-categories.edit', $category->id) }}" class="rounded-xl border border-white/20 px-4 py-2 text-sm text-white/80 hover:text-white">
                                Editar
                            </a>
                            <form action="{{ route('cantina-categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta categoría?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-xl border border-rose-500/40 px-4 py-2 text-sm text-rose-200 hover:text-white">
                                    Borrar
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-sm text-white/60">
                        Aún no hay categorías creadas.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</body>
</html>
