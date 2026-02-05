<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo artículo · Cantina</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" />
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-0 space-y-8">
        <a href="{{ route('admin.new-panel', ['section' => 'cantina']) }}" class="inline-flex items-center gap-2 text-sm text-white/70 hover:text-white transition">
            <span class="text-lg">←</span> Volver al panel
        </a>

        <div class="rounded-3xl border border-white/10 bg-white/5 backdrop-blur p-8 shadow-2xl">
            <div class="space-y-2 mb-8">
                <p class="text-xs uppercase tracking-[0.35em] text-white/60">Cantina</p>
                <h1 class="text-3xl font-semibold">Crear artículo</h1>
                <p class="text-white/60 text-sm">Agrega un artículo con nombre, precio y categoría.</p>
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

            <form action="{{ route('cantina-items.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-semibold text-white/80 mb-2">Nombre del artículo</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-amber-400 focus:ring-amber-400" />
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-white/80 mb-2">Descripción</label>
                    <textarea id="description" name="description" rows="4"
                              class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-amber-400 focus:ring-amber-400">{{ old('description') }}</textarea>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-sm font-semibold text-white/80 mb-2">Precio</label>
                        <input type="number" step="0.01" id="price" name="price" value="{{ old('price') }}" required
                               class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-amber-400 focus:ring-amber-400" />
                    </div>
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-white/80 mb-2">Categoría</label>
                        <select id="category_id" name="category_id" required
                                class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-amber-400 focus:ring-amber-400">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="image" class="block text-sm font-semibold text-white/80 mb-2">Imagen (opcional)</label>
                    <input type="file" id="image" name="image"
                           class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white file:mr-4 file:rounded-xl file:border-0 file:bg-white/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-white/20" />
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <input type="hidden" name="visible" value="0">
                        <input class="h-4 w-4 rounded text-amber-400 focus:ring-amber-400" type="checkbox" id="visible" name="visible" value="1" {{ old('visible', 1) ? 'checked' : '' }}>
                        <span class="text-sm text-white/80">Visible</span>
                    </label>
                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <input type="hidden" name="featured_on_cover" value="0">
                        <input class="h-4 w-4 rounded text-amber-400 focus:ring-amber-400" type="checkbox" id="featured_on_cover" name="featured_on_cover" value="1" {{ old('featured_on_cover') ? 'checked' : '' }}>
                        <span class="text-sm text-white/80">Destacar en portada</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.new-panel', ['section' => 'cantina']) }}" class="rounded-2xl border border-white/20 px-5 py-2 text-sm font-semibold text-white/80 hover:text-white">
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
