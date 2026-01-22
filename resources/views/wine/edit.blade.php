<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar bebida de café</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" />
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-0 space-y-8">
        <a href="{{ route('admin.new-panel', ['section' => 'wines']) }}" class="inline-flex items-center gap-2 text-sm text-white/70 hover:text-white transition">
            <span class="text-lg">←</span> Volver al panel
        </a>

        <div class="rounded-3xl border border-white/10 bg-white/5 backdrop-blur p-8 shadow-2xl">
            <div class="space-y-2 mb-8">
                <p class="text-xs uppercase tracking-[0.35em] text-white/60">Café & Brunch</p>
                <h1 class="text-3xl font-semibold">Editar bebida</h1>
                <p class="text-white/60 text-sm">Modifica origen, métodos y maridajes del café.</p>
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

            <form action="{{ route('wines.update', $wine) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-semibold text-white/80 mb-2">Nombre</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $wine->name) }}" required
                           class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-amber-400 focus:ring-amber-400" />
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-white/80 mb-2">Descripción</label>
                    <textarea id="description" name="description" rows="4"
                              class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-amber-400 focus:ring-amber-400">{{ old('description', $wine->description) }}</textarea>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-sm font-semibold text-white/80 mb-2">Precio</label>
                        <input type="number" step="0.01" id="price" name="price" value="{{ old('price', $wine->price) }}" required
                               class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-amber-400 focus:ring-amber-400" />
                    </div>
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-white/80 mb-2">Categoría</label>
                        <select id="category_id" name="category_id" required
                                class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-amber-400 focus:ring-amber-400">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $wine->category_id) == $category->id ? 'selected' : '' }}>
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
                                        <option value="{{ $subcategory->id }}" {{ old('subcategory_id', $wine->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                            {{ $subcategory->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                    <p class="text-xs text-white/50 mt-2">Solo se guardan subcategorías que pertenezcan a la categoría elegida.</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="type_id" class="block text-sm font-semibold text-white/80 mb-2">Método</label>
                        <select id="type_id" name="type_id"
                                class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-amber-400 focus:ring-amber-400">
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" {{ old('type_id', $wine->type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="region_id" class="block text-sm font-semibold text-white/80 mb-2">Origen</label>
                        <select id="region_id" name="region_id"
                                class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-amber-400 focus:ring-amber-400">
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}" {{ old('region_id', $wine->region_id) == $region->id ? 'selected' : '' }}>
                                    {{ $region->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="grapes" class="block text-sm font-semibold text-white/80 mb-2">Notas / toppings</label>
                    <select id="grapes" name="grapes[]" multiple
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-amber-400 focus:ring-amber-400 min-h-[120px]">
                        @foreach($grapes as $grape)
                            <option value="{{ $grape->id }}" {{ in_array($grape->id, old('grapes', $wine->grapes->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ $grape->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="dishes" class="block text-sm font-semibold text-white/80 mb-2">Platos sugeridos</label>
                    <select id="dishes" name="dishes[]" multiple
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-amber-400 focus:ring-amber-400 min-h-[120px]">
                        @foreach($dishes as $dish)
                            <option value="{{ $dish->id }}" {{ in_array($dish->id, old('dishes', $wine->dishes->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ $dish->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="extra_ids" class="block text-sm font-semibold text-white/80 mb-2">Opciones sugeridas</label>
                    <select id="extra_ids" name="extra_ids[]" multiple
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-amber-400 focus:ring-amber-400 min-h-[120px]">
                        @php
                            $selectedExtras = collect(old('extra_ids', $wine->extras->pluck('id')->toArray()))
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
                    <p class="text-xs text-white/50 mt-2">Ejemplo: leche vegetal, toppings premium, shots.</p>
                </div>

                <div>
                    <label for="tax_ids" class="block text-sm font-semibold text-white/80 mb-2">Impuestos aplicados</label>
                    @php
                        $selectedTaxes = collect(old('tax_ids', $wine->taxes->pluck('id')->toArray()))
                            ->map(fn($value) => (int) $value);
                    @endphp
                    <select id="tax_ids" name="tax_ids[]" multiple
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-amber-400 focus:ring-amber-400 min-h-[120px]">
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
                        $selectedPrepLabel = old('prep_label_id', $wine->prepLabels->first()?->id);
                    @endphp
                    <select id="prep_label_id" name="prep_label_id"
                            class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-amber-400 focus:ring-amber-400">
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
                               class="block w-full rounded-2xl border border-dashed border-white/20 bg-white/5 px-4 py-3 text-white focus:border-amber-400 focus:ring-amber-400" />
                        @if($wine->image)
                            <img src="{{ asset('storage/' . $wine->image) }}" alt="{{ $wine->name }}"
                                 class="w-full rounded-2xl border border-white/10 object-cover max-h-60">
                        @endif
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <input type="hidden" name="visible" value="0">
                        <input type="checkbox" id="visible" name="visible" value="1" {{ old('visible', $wine->visible) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-white/30 bg-transparent text-amber-400 focus:ring-amber-400" />
                        <label for="visible" class="text-sm text-white/80">Mostrar en la lista pública</label>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <input type="hidden" name="featured_on_cover" value="0">
                        <input type="checkbox" id="featured_on_cover" name="featured_on_cover" value="1" {{ old('featured_on_cover', $wine->featured_on_cover) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-white/30 bg-transparent text-amber-400 focus:ring-amber-400" />
                        <label for="featured_on_cover" class="text-sm text-white/80">Destacar en portada</label>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 pt-4">
                    <p class="text-xs text-white/50">Los cambios se aplican al guardar.</p>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-full bg-amber-400 px-6 py-3 font-semibold text-slate-900 shadow-lg shadow-amber-400/40 hover:bg-amber-300 transition">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>
