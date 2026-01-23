@php
    $specialActive = old('active', $special?->active ?? true);
    $selectedGeneralDays = old('days_of_week', $special?->days_of_week ?? []);
    $selectedGeneralDays = is_array($selectedGeneralDays) ? $selectedGeneralDays : [];
@endphp

<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="form-label">Nombre del especial</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $special?->name) }}" required>
        </div>
        <div class="flex items-end">
            <div class="form-check form-switch">
                <input type="hidden" name="active" value="0">
                <input class="form-check-input" type="checkbox" id="specialActive" name="active" value="1" {{ $specialActive ? 'checked' : '' }}>
                <label class="form-check-label" for="specialActive">Especial activo</label>
            </div>
        </div>
    </div>

    <div class="border rounded-3 p-4 space-y-3">
        <h4 class="text-sm font-semibold text-slate-700">Disponibilidad general</h4>
        <div class="flex flex-wrap gap-2">
            @foreach($days as $dayValue => $dayLabel)
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="days_of_week[]" value="{{ $dayValue }}"
                        {{ in_array($dayValue, $selectedGeneralDays) ? 'checked' : '' }}>
                    <span>{{ $dayLabel }}</span>
                </label>
            @endforeach
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="form-label">Hora inicio</label>
                <input type="time" name="starts_at" class="form-control" value="{{ old('starts_at', $special?->starts_at) }}">
            </div>
            <div>
                <label class="form-label">Hora fin</label>
                <input type="time" name="ends_at" class="form-control" value="{{ old('ends_at', $special?->ends_at) }}">
            </div>
        </div>
        <p class="text-xs text-slate-500">Si dejas los días u horas vacíos, el especial se considera disponible todo el día.</p>
    </div>

    @foreach($scopes as $scopeKey => $scope)
        <div class="border rounded-3 p-4 space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold text-slate-700">{{ $scope['label'] }}</h4>
                <span class="text-xs text-slate-400 uppercase tracking-[0.3em]">Categorías</span>
            </div>

            @if($scope['categories']->isEmpty())
                <p class="text-sm text-slate-500">No hay categorías con items visibles en esta vista.</p>
            @else
                @foreach($scope['categories'] as $category)
                    @php
                        $categoryKey = $scopeKey . ':' . $category->id;
                        $selectedCategory = $selectedCategories[$categoryKey] ?? null;
                        $categoryActive = old("categories.$scopeKey.$category->id.active", $selectedCategory?->active ?? false);
                        $categoryDays = old("categories.$scopeKey.$category->id.days_of_week", $selectedCategory?->days_of_week ?? []);
                        $categoryDays = is_array($categoryDays) ? $categoryDays : [];
                        $categoryExpanded = (bool) $categoryActive;
                        $panelId = 'special-items-' . $scopeKey . '-' . $category->id;
                    @endphp
                    <div class="rounded-2xl border border-slate-200 p-4 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="categories[{{ $scopeKey }}][{{ $category->id }}][active]" value="0">
                                <input type="checkbox"
                                       name="categories[{{ $scopeKey }}][{{ $category->id }}][active]"
                                       value="1"
                                       class="form-check-input"
                                       data-category-toggle="{{ $scopeKey }}-{{ $category->id }}"
                                       {{ $categoryActive ? 'checked' : '' }}>
                                <span class="font-semibold text-slate-800">{{ $category->name }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-400">{{ $category->{$scope['item_relation']}->count() }} items</span>
                                <button type="button"
                                    class="text-xs font-semibold text-slate-500 hover:text-slate-700"
                                    data-collapse-toggle="{{ $scopeKey }}-{{ $category->id }}"
                                    aria-controls="{{ $panelId }}"
                                    aria-expanded="{{ $categoryExpanded ? 'true' : 'false' }}">
                                    {{ $categoryExpanded ? 'Ocultar items' : 'Ver items' }}
                                </button>
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-3">
                            <div class="md:col-span-2">
                                <label class="form-label">Días (override)</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($days as $dayValue => $dayLabel)
                                        <label class="inline-flex items-center gap-2 text-xs">
                                            <input type="checkbox" name="categories[{{ $scopeKey }}][{{ $category->id }}][days_of_week][]" value="{{ $dayValue }}"
                                                {{ in_array($dayValue, $categoryDays) ? 'checked' : '' }}>
                                            <span>{{ $dayLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Hora inicio</label>
                                <input type="time" name="categories[{{ $scopeKey }}][{{ $category->id }}][starts_at]" class="form-control"
                                       value="{{ old('categories.' . $scopeKey . '.' . $category->id . '.starts_at', $selectedCategory?->starts_at) }}">
                            </div>
                            <div>
                                <label class="form-label">Hora fin</label>
                                <input type="time" name="categories[{{ $scopeKey }}][{{ $category->id }}][ends_at]" class="form-control"
                                       value="{{ old('categories.' . $scopeKey . '.' . $category->id . '.ends_at', $selectedCategory?->ends_at) }}">
                            </div>
                        </div>

                        <div id="{{ $panelId }}"
                             class="pt-3 border-t border-slate-200 space-y-3 {{ $categoryExpanded ? '' : 'hidden' }}"
                             data-category-items="{{ $scopeKey }}-{{ $category->id }}"
                             data-collapse-panel="{{ $scopeKey }}-{{ $category->id }}">
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Items visibles</p>
                            <div class="grid gap-3 md:grid-cols-2">
                                @foreach($category->{$scope['item_relation']} as $item)
                                    @php
                                        $itemKey = $scopeKey . ':' . $item->id;
                                        $selectedItem = $selectedItems[$itemKey] ?? null;
                                        $itemActive = old('items.' . $scopeKey . '.' . $item->id . '.active', $selectedItem?->active ?? false);
                                        $itemDays = old('items.' . $scopeKey . '.' . $item->id . '.days_of_week', $selectedItem?->days_of_week ?? []);
                                        $itemDays = is_array($itemDays) ? $itemDays : [];
                                    @endphp
                                    <div class="rounded-xl border border-slate-200 p-3 space-y-2">
                                        <div class="flex items-center gap-2">
                                            <input type="hidden" name="items[{{ $scopeKey }}][{{ $item->id }}][active]" value="0">
                                            <input type="checkbox" name="items[{{ $scopeKey }}][{{ $item->id }}][active]" value="1" class="form-check-input"
                                                {{ $itemActive ? 'checked' : '' }}>
                                            <span class="text-sm font-semibold text-slate-800">{{ $item->name }}</span>
                                        </div>
                                        <input type="hidden" name="items[{{ $scopeKey }}][{{ $item->id }}][category_id]" value="{{ $category->id }}">
                                        <div class="grid gap-2 md:grid-cols-3">
                                            <div>
                                                <label class="form-label text-xs">Tipo de oferta</label>
                                                @php
                                                    $offerType = old('items.' . $scopeKey . '.' . $item->id . '.offer_type', $selectedItem?->offer_type);
                                                @endphp
                                                <select name="items[{{ $scopeKey }}][{{ $item->id }}][offer_type]" class="form-control form-control-sm">
                                                    <option value="">Sin oferta</option>
                                                    <option value="percent" {{ $offerType === 'percent' ? 'selected' : '' }}>% descuento</option>
                                                    <option value="fixed_price" {{ $offerType === 'fixed_price' ? 'selected' : '' }}>Precio fijo</option>
                                                    <option value="two_for_one" {{ $offerType === 'two_for_one' ? 'selected' : '' }}>2x1</option>
                                                    <option value="custom" {{ $offerType === 'custom' ? 'selected' : '' }}>Personalizada</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label text-xs">Valor</label>
                                                <input type="text"
                                                       name="items[{{ $scopeKey }}][{{ $item->id }}][offer_value]"
                                                       class="form-control form-control-sm"
                                                       value="{{ old('items.' . $scopeKey . '.' . $item->id . '.offer_value', $selectedItem?->offer_value) }}"
                                                       placeholder="Ej: 50 o 9.99">
                                            </div>
                                            <div class="md:col-span-3">
                                                <label class="form-label text-xs">Texto de oferta</label>
                                                <input type="text"
                                                       name="items[{{ $scopeKey }}][{{ $item->id }}][offer_text]"
                                                       class="form-control form-control-sm"
                                                       value="{{ old('items.' . $scopeKey . '.' . $item->id . '.offer_text', $selectedItem?->offer_text) }}"
                                                       placeholder="Ej: 2x1 en margaritas · 3pm a 6pm">
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($days as $dayValue => $dayLabel)
                                                <label class="inline-flex items-center gap-1 text-[11px]">
                                                    <input type="checkbox" name="items[{{ $scopeKey }}][{{ $item->id }}][days_of_week][]" value="{{ $dayValue }}"
                                                        {{ in_array($dayValue, $itemDays) ? 'checked' : '' }}>
                                                    <span>{{ $dayLabel }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="time" name="items[{{ $scopeKey }}][{{ $item->id }}][starts_at]" class="form-control form-control-sm"
                                                   value="{{ old('items.' . $scopeKey . '.' . $item->id . '.starts_at', $selectedItem?->starts_at) }}">
                                            <input type="time" name="items[{{ $scopeKey }}][{{ $item->id }}][ends_at]" class="form-control form-control-sm"
                                                   value="{{ old('items.' . $scopeKey . '.' . $item->id . '.ends_at', $selectedItem?->ends_at) }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach
</div>
