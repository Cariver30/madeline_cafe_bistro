<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo plato · Panel creativo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.css">
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-0 space-y-8">
        <a href="{{ route('admin.new-panel', ['section' => 'menu']) }}" class="inline-flex items-center gap-2 text-sm text-white/70 hover:text-white transition">
            <span class="text-lg">←</span> Volver al panel
        </a>

        <div class="rounded-3xl border border-white/10 bg-white/5 backdrop-blur p-8 shadow-2xl">
            <div class="space-y-2 mb-8">
                <p class="text-xs uppercase tracking-[0.35em] text-white/60">Menú creativo</p>
                <h1 class="text-3xl font-semibold">Agregar nuevo plato</h1>
                <p class="text-white/60 text-sm">Completa la ficha para publicar un plato dentro de la categoría seleccionada.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-500/40 bg-rose-500/10 p-4 text-sm text-rose-100">
                    <p class="font-semibold mb-2">Revisa la información:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('dishes.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-semibold text-white/80 mb-2">Nombre del plato</label>
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
                        <input type="number" id="price" name="price" step="0.01" value="{{ old('price') }}" required
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
                    <label for="subcategory_id" class="block text-sm font-semibold text-white/80 mb-2">Subcategoría (opcional)</label>
                    <select id="subcategory_id" name="subcategory_id"
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-amber-400 focus:ring-amber-400">
                        <option value="">Sin subcategoría</option>
                        @foreach($categories as $category)
                            @if($category->subcategories->count())
                                <optgroup label="{{ $category->name }}">
                                    @foreach($category->subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}" {{ old('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                                            {{ $subcategory->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                    <p class="text-xs text-white/50 mt-2">Solo se guardan subcategorías que pertenezcan a la categoría elegida.</p>
                </div>

                <div>
                    <label for="recommended_dishes" class="block text-sm font-semibold text-white/80 mb-2">Combínalo con otros platos</label>
                    <p class="text-xs text-white/50 mb-2">Selecciona los platos que aparecerán como acompañantes recomendados en el menú público y en la app del gerente.</p>
                    <select id="recommended_dishes" name="recommended_dishes[]" multiple
                            data-tom-select
                            placeholder="Busca y selecciona platos"
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 text-slate-900 text-base">
                        @php
                            $oldRecommendations = collect(old('recommended_dishes', []))->map(fn($value) => (int) $value);
                        @endphp
                        @foreach($allDishes as $availableDish)
                            <option value="{{ $availableDish->id }}" {{ $oldRecommendations->contains($availableDish->id) ? 'selected' : '' }}>
                                {{ $availableDish->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="extra_ids" class="block text-sm font-semibold text-white/80 mb-2">Opciones sugeridas</label>
                    <p class="text-xs text-white/50 mb-2">Selecciona opciones como aguacate, extra shot o toppings. Puedes crearlas desde la pestaña “Opciones”.</p>
                    <select id="extra_ids" name="extra_ids[]" multiple
                            data-tom-select
                            placeholder="Selecciona opciones"
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 text-slate-900 text-base">
                        @php
                            $oldExtras = collect(old('extra_ids', []))->map(fn($value) => (int) $value);
                            $groupedExtras = $availableExtras->groupBy(fn($extra) => $extra->group_name ?: 'Sin grupo');
                        @endphp
                        @foreach($groupedExtras as $groupName => $extras)
                            <optgroup label="{{ $groupName }}">
                                @foreach($extras as $extra)
                                    <option value="{{ $extra->id }}" {{ $oldExtras->contains($extra->id) ? 'selected' : '' }}>
                                        {{ $extra->name }} · ${{ number_format($extra->price, 2) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tax_ids" class="block text-sm font-semibold text-white/80 mb-2">Impuestos aplicados</label>
                    <p class="text-xs text-white/50 mb-2">Puedes asignar varios impuestos a este plato.</p>
                    <select id="tax_ids" name="tax_ids[]" multiple
                            data-tom-select
                            placeholder="Selecciona impuestos"
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 text-slate-900 text-base">
                        @php
                            $oldTaxes = collect(old('tax_ids', []))->map(fn($value) => (int) $value);
                        @endphp
                        @foreach($taxes as $tax)
                            <option value="{{ $tax->id }}" {{ $oldTaxes->contains($tax->id) ? 'selected' : '' }}>
                                {{ $tax->name }} · {{ number_format($tax->rate, 2) }}%
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="prep_label_id" class="block text-sm font-semibold text-white/80 mb-2">Label de preparación</label>
                    <p class="text-xs text-white/50 mb-2">Define en qué pantalla e impresora aparecerá este plato.</p>
                    <select id="prep_label_id" name="prep_label_id"
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-amber-400 focus:ring-amber-400">
                        <option value="">Sin label</option>
                        @foreach($prepAreas as $area)
                            @if($area->labels->count())
                                <optgroup label="{{ $area->name }}">
                                    @foreach($area->labels as $label)
                                        <option value="{{ $label->id }}" {{ old('prep_label_id') == $label->id ? 'selected' : '' }}>
                                            {{ $label->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="image" class="block text-sm font-semibold text-white/80 mb-2">Imagen destacada</label>
                    <input type="file" id="image" name="image"
                           class="block w-full rounded-2xl border border-dashed border-white/20 bg-white/5 px-4 py-3 text-white focus:border-amber-400 focus:ring-amber-400" />
                    <p class="text-xs text-white/50 mt-2">Formato recomendado 4:3 · JPG o PNG en alta resolución.</p>
                </div>

                <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                    <input type="checkbox" id="featured_on_cover" name="featured_on_cover" value="1" {{ old('featured_on_cover') ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-white/30 bg-transparent text-amber-400 focus:ring-amber-400" />
                    <label for="featured_on_cover" class="text-sm text-white/80">
                        Mostrar este plato en la portada (usa el bloque activo de la categoría).
                    </label>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 pt-4">
                    <p class="text-xs text-white/50">Los cambios aparecen inmediatamente en el menú público.</p>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-full bg-amber-400 px-6 py-3 font-semibold text-slate-900 shadow-lg shadow-amber-400/40 hover:bg-amber-300 transition">
                        Guardar plato
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-tom-select]').forEach(select => {
                new TomSelect(select, {
                    plugins: ['remove_button', 'checkbox_options'],
                    maxItems: null,
                    placeholder: select.getAttribute('placeholder') || 'Selecciona platos',
                    highlight: true,
                    render: {
                        option: function(data, escape) {
                            return `<div class="py-1 px-2">${escape(data.text)}</div>`;
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
