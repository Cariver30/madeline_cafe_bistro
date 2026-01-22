<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar bebida · Barra creativa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" />
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-0 space-y-8">
        <a href="{{ route('admin.new-panel', ['section' => 'cocktails']) }}" class="inline-flex items-center gap-2 text-sm text-white/70 hover:text-white transition">
            <span class="text-lg">←</span> Volver al panel
        </a>

        <div class="rounded-3xl border border-white/10 bg-white/5 backdrop-blur p-8 shadow-2xl">
            <div class="space-y-2 mb-8">
                <p class="text-xs uppercase tracking-[0.35em] text-white/60">Mixología</p>
                <h1 class="text-3xl font-semibold">Editar bebida</h1>
                <p class="text-white/60 text-sm">Actualiza información, relación de platos y visibilidad.</p>
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

            <form action="{{ route('cocktails.update', $cocktail) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-semibold text-white/80 mb-2">Nombre</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $cocktail->name) }}" required
                           class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-pink-400 focus:ring-pink-400" />
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-white/80 mb-2">Descripción</label>
                    <textarea id="description" name="description" rows="4"
                              class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-pink-400 focus:ring-pink-400">{{ old('description', $cocktail->description) }}</textarea>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-sm font-semibold text-white/80 mb-2">Precio</label>
                        <input type="number" step="0.01" id="price" name="price" value="{{ old('price', $cocktail->price) }}" required
                               class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-pink-400 focus:ring-pink-400" />
                    </div>
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-white/80 mb-2">Categoría</label>
                        <select id="category_id" name="category_id" required
                                class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-pink-400 focus:ring-pink-400">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $cocktail->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="subcategory_id" class="block text-sm font-semibold text-white/80 mb-2">Subcategoría (opcional)</label>
                    <select id="subcategory_id" name="subcategory_id"
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-pink-400 focus:ring-pink-400">
                        <option value="">Sin subcategoría</option>
                        @foreach($categories as $category)
                            @if($category->subcategories->count())
                                <optgroup label="{{ $category->name }}">
                                    @foreach($category->subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}" {{ old('subcategory_id', $cocktail->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
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
                    <label for="dishes" class="block text-sm font-semibold text-white/80 mb-2">Platos sugeridos</label>
                    <select id="dishes" name="dishes[]" multiple
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-pink-400 focus:ring-pink-400 min-h-[120px]">
                        @foreach($dishes as $dish)
                            <option value="{{ $dish->id }}" {{ in_array($dish->id, old('dishes', $cocktail->dishes->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ $dish->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-white/50 mt-2">Mantén presionado Cmd/Ctrl para seleccionar múltiples.</p>
                </div>

                <div>
                    <label for="extra_ids" class="block text-sm font-semibold text-white/80 mb-2">Opciones sugeridas</label>
                    <select id="extra_ids" name="extra_ids[]" multiple
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-pink-400 focus:ring-pink-400 min-h-[120px]">
                        @php
                            $selectedExtras = collect(old('extra_ids', $cocktail->extras->pluck('id')->toArray()))
                                ->map(fn($value) => (int) $value);
                            $groupedExtras = $availableExtras->groupBy(fn($extra) => $extra->group_name ?: 'Sin grupo');
                        @endphp
                        @foreach($groupedExtras as $groupName => $extras)
                            <optgroup label="{{ $groupName }}">
                                @foreach($extras as $extra)
                                    <option value="{{ $extra->id }}" {{ $selectedExtras->contains($extra->id) ? 'selected' : '' }}>
                                        {{ $extra->name }} · ${{ number_format($extra->price, 2) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <p class="text-xs text-white/50 mt-2">Define opciones como jarabes, top-ups o complementos.</p>
                </div>

                <div>
                    <label for="tax_ids" class="block text-sm font-semibold text-white/80 mb-2">Impuestos aplicados</label>
                    @php
                        $selectedTaxes = collect(old('tax_ids', $cocktail->taxes->pluck('id')->toArray()))
                            ->map(fn($value) => (int) $value);
                    @endphp
                    <select id="tax_ids" name="tax_ids[]" multiple
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-pink-400 focus:ring-pink-400 min-h-[120px]">
                        @foreach($taxes as $tax)
                            <option value="{{ $tax->id }}" {{ $selectedTaxes->contains($tax->id) ? 'selected' : '' }}>
                                {{ $tax->name }} · {{ number_format($tax->rate, 2) }}%
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-white/50 mt-2">Estos impuestos se suman a los de la categoría.</p>
                </div>

                <div>
                    <label for="prep_label_id" class="block text-sm font-semibold text-white/80 mb-2">Label de preparación</label>
                    <p class="text-xs text-white/50 mb-2">Define en qué pantalla e impresora aparecerá este ítem.</p>
                    @php
                        $selectedPrepLabel = old('prep_label_id', $cocktail->prepLabels->first()?->id);
                    @endphp
                    <select id="prep_label_id" name="prep_label_id"
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-pink-400 focus:ring-pink-400">
                        <option value="">Sin label</option>
                        @foreach($prepAreas as $area)
                            @if($area->labels->count())
                                <optgroup label="{{ $area->name }}">
                                    @foreach($area->labels as $label)
                                        <option value="{{ $label->id }}" {{ (int) $selectedPrepLabel === $label->id ? 'selected' : '' }}>
                                            {{ $label->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="image" class="block text-sm font-semibold text-white/80 mb-2">Imagen (opcional)</label>
                    <div class="flex flex-col gap-3">
                        <input type="file" id="image" name="image"
                               class="block w-full rounded-2xl border border-dashed border-white/20 bg-white/5 px-4 py-3 text-white focus:border-pink-400 focus:ring-pink-400" />
                        @if($cocktail->image)
                            <img src="{{ asset('storage/' . $cocktail->image) }}" alt="{{ $cocktail->name }}"
                                 class="w-full rounded-2xl border border-white/10 object-cover max-h-60">
                        @endif
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <input type="hidden" name="visible" value="0">
                        <input type="checkbox" id="visible" name="visible" value="1" {{ old('visible', $cocktail->visible) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-white/30 bg-transparent text-pink-400 focus:ring-pink-400" />
                        <label for="visible" class="text-sm text-white/80">Mostrar en la lista pública</label>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <input type="hidden" name="featured_on_cover" value="0">
                        <input type="checkbox" id="featured_on_cover" name="featured_on_cover" value="1" {{ old('featured_on_cover', $cocktail->featured_on_cover) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-white/30 bg-transparent text-pink-400 focus:ring-pink-400" />
                        <label for="featured_on_cover" class="text-sm text-white/80">Destacar en portada</label>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 pt-4">
                    <p class="text-xs text-white/50">Los cambios se aplican en tiempo real.</p>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-full bg-pink-400 px-6 py-3 font-semibold text-slate-900 shadow-lg shadow-pink-400/40 hover:bg-pink-300 transition">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>
