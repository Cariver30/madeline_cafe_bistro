<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva categoría · Cantina</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" />
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-0 space-y-8">
        <a href="{{ route('cantina-categories.index') }}" class="inline-flex items-center gap-2 text-sm text-white/70 hover:text-white transition">
            <span class="text-lg">←</span> Volver a categorías
        </a>

        <div class="rounded-3xl border border-white/10 bg-white/5 backdrop-blur p-8 shadow-2xl">
            <div class="space-y-2 mb-8">
                <p class="text-xs uppercase tracking-[0.35em] text-white/60">Cantina</p>
                <h1 class="text-3xl font-semibold">Crear categoría</h1>
                <p class="text-white/60 text-sm">Organiza la cantina con nombres públicos y visibilidad en portada.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-500/40 bg-rose-500/10 p-4 text-sm text-rose-100">
                    <p class="font-semibold mb-2">Revisa estos campos:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('cantina-categories.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-semibold text-white/80 mb-2">Nombre interno</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-amber-400 focus:ring-amber-400" />
                </div>

                <div>
                    <label for="cover_title" class="block text-sm font-semibold text-white/80 mb-2">Nombre público</label>
                    <input type="text" id="cover_title" name="cover_title" value="{{ old('cover_title') }}"
                           class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-amber-400 focus:ring-amber-400" />
                </div>

                <div>
                    <label for="cover_subtitle" class="block text-sm font-semibold text-white/80 mb-2">Descripción breve</label>
                    <input type="text" id="cover_subtitle" name="cover_subtitle" value="{{ old('cover_subtitle') }}"
                           class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-amber-400 focus:ring-amber-400" />
                </div>

                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                    <input type="hidden" name="show_on_cover" value="0">
                    <input class="h-4 w-4 rounded text-amber-400 focus:ring-amber-400" type="checkbox" id="show_on_cover" name="show_on_cover" value="1" {{ old('show_on_cover') ? 'checked' : '' }}>
                    <span class="text-sm text-white/80">Mostrar en portada</span>
                </label>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('cantina-categories.index') }}" class="rounded-2xl border border-white/20 px-5 py-2 text-sm font-semibold text-white/80 hover:text-white">
                        Cancelar
                    </a>
                    <button type="submit" class="rounded-2xl bg-amber-400 px-6 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-300">
                        Crear
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
