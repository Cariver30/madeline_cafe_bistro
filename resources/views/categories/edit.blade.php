<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar categoría · Menú creativo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" />
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-0 space-y-8">
        <a href="{{ route('admin.new-panel', ['section' => 'menu', 'expand' => 'dish-categories']) }}"
           class="inline-flex items-center gap-2 text-sm text-white/70 hover:text-white transition">
            <span class="text-lg">←</span> Volver al panel
        </a>

        <div class="rounded-3xl border border-white/10 bg-white/5 backdrop-blur p-8 shadow-2xl space-y-8">
            <div class="space-y-2">
                <p class="text-xs uppercase tracking-[0.35em] text-white/60">Categorías</p>
                <h1 class="text-3xl font-semibold">Editar categoría</h1>
                <p class="text-white/60 text-sm">Este nombre impacta la navegación del menú y la portada.</p>
            </div>

            @if ($errors->any())
                <div class="rounded-2xl border border-rose-500/40 bg-rose-500/10 p-4 text-sm text-rose-100">
                    <p class="font-semibold mb-2">Revisa la información:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-semibold text-white/80 mb-2">Nombre interno</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required
                           class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-emerald-400 focus:ring-emerald-400" />
                    <p class="text-xs text-white/50 mt-2">Se usa en el panel y como fallback si no defines un nombre público.</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="cover_title" class="block text-sm font-semibold text-white/80 mb-2">Nombre público</label>
                        <input type="text" id="cover_title" name="cover_title" value="{{ old('cover_title', $category->cover_title) }}"
                               class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-emerald-400 focus:ring-emerald-400" />
                        <p class="text-xs text-white/50 mt-2">Aparece en los tabs y tarjetas del cover.</p>
                    </div>
                    <div>
                        <label for="cover_subtitle" class="block text-sm font-semibold text-white/80 mb-2">Descripción breve</label>
                        <input type="text" id="cover_subtitle" name="cover_subtitle" value="{{ old('cover_subtitle', $category->cover_subtitle) }}"
                               class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-emerald-400 focus:ring-emerald-400" />
                        <p class="text-xs text-white/50 mt-2">Se muestra debajo del título en la portada.</p>
                    </div>
                </div>

                <div>
                    <label for="tax_ids" class="block text-sm font-semibold text-white/80 mb-2">Impuestos aplicados</label>
                    @php
                        $selectedTaxes = collect(old('tax_ids', $category->taxes->pluck('id')->all()))
                            ->map(fn($value) => (int) $value);
                    @endphp
                    <select id="tax_ids" name="tax_ids[]" multiple
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 text-slate-900 text-base">
                        @foreach($taxes as $tax)
                            <option value="{{ $tax->id }}" {{ $selectedTaxes->contains($tax->id) ? 'selected' : '' }}>
                                {{ $tax->name }} · {{ number_format($tax->rate, 2) }}%
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-white/50 mt-2">Estos impuestos se heredan por los platos sin configuración individual.</p>
                </div>

                <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                    <input type="hidden" name="show_on_cover" value="0">
                    <input type="checkbox" id="show_on_cover" name="show_on_cover" value="1"
                           {{ old('show_on_cover', $category->show_on_cover) ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-white/30 bg-transparent text-emerald-400 focus:ring-emerald-400" />
                    <label for="show_on_cover" class="text-sm text-white/80">
                        Mostrar esta categoría en la portada / tabs públicos
                    </label>
                </div>

                <div class="flex items-center justify-between gap-4 pt-4">
                    <p class="text-xs text-white/50">Guardamos todo al presionar “Actualizar categoría”.</p>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-full bg-emerald-400 px-6 py-3 font-semibold text-slate-900 shadow-lg shadow-emerald-400/30 hover:bg-emerald-300 transition">
                        Actualizar categoría
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>
