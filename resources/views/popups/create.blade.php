@php
    $tabLabelMenu = $settings->tab_label_menu ?? $settings->button_label_menu ?? 'Menú';
    $tabLabelCocktails = $settings->tab_label_cocktails ?? $settings->button_label_cocktails ?? 'Cócteles';
    $tabLabelWines = $settings->tab_label_wines ?? $settings->button_label_wines ?? 'Bebidas';
    $tabLabelEvents = $settings->tab_label_events ?? 'Eventos';

    $viewOptions = collect([
        'cover' => [
            'label' => $settings->tab_label_cover ?? 'Portada / landing',
            'visible' => true,
        ],
        'menu' => [
            'label' => $tabLabelMenu,
            'visible' => $settings->show_tab_menu ?? true,
        ],
        'cocktails' => [
            'label' => $tabLabelCocktails,
            'visible' => $settings->show_tab_cocktails ?? true,
        ],
        'coffee' => [
            'label' => $tabLabelWines,
            'visible' => $settings->show_tab_wines ?? true,
        ],
        'events' => [
            'label' => $tabLabelEvents,
            'visible' => $settings->show_tab_events ?? true,
        ],
    ])->filter(fn($option) => $option['visible'])->all();

    $availableViews = array_keys($viewOptions);
    $defaultView = old('view', $availableViews[0] ?? 'cover');
    $weekDays = [
        ['value' => 0, 'label' => 'Domingo'],
        ['value' => 1, 'label' => 'Lunes'],
        ['value' => 2, 'label' => 'Martes'],
        ['value' => 3, 'label' => 'Miércoles'],
        ['value' => 4, 'label' => 'Jueves'],
        ['value' => 5, 'label' => 'Viernes'],
        ['value' => 6, 'label' => 'Sábado'],
    ];
    $selectedDays = collect(old('repeat_days', []))->map(fn ($day) => (int) $day)->all();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Pop-up</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="max-w-4xl mx-auto py-10 px-4">
        <a href="{{ route('admin.new-panel', ['section' => 'popups']) }}" class="inline-flex items-center text-sm text-slate-500 hover:text-slate-900 mb-4">&larr; Volver al panel</a>
        <div class="bg-white shadow-xl rounded-3xl p-8 space-y-6">
            <div class="space-y-1">
                <p class="text-xs uppercase tracking-[0.35em] text-amber-500">Promociones</p>
                <h1 class="text-3xl font-semibold">Nuevo pop-up</h1>
                <p class="text-sm text-slate-500">Define dónde y cuándo se mostrará tu anuncio. Puedes programar fechas y días específicos.</p>
            </div>

            @if ($errors->any())
                <div class="border border-rose-200 bg-rose-50 text-rose-700 rounded-2xl p-4 text-sm">
                    <p class="font-semibold mb-2">Debes corregir lo siguiente:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.popups.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-semibold text-slate-700 mb-1">Título</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" required
                               class="w-full rounded-2xl border border-slate-200 focus:ring-amber-400 focus:border-amber-400 px-4 py-2.5">
                    </div>
                    <div>
                        <label for="image" class="block text-sm font-semibold text-slate-700 mb-1">Imagen</label>
                        <input type="file" id="image" name="image" required
                               class="w-full rounded-2xl border border-dashed border-slate-300 px-4 py-2.5 bg-slate-50">
                        <p class="text-xs text-slate-500 mt-1">Preferible 16:9 o 4:5 en JPG/PNG.</p>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700 mb-2">¿Dónde debe mostrarse?</p>
                    <div class="grid sm:grid-cols-2 gap-3">
                        @foreach($viewOptions as $value => $option)
                            <label class="border rounded-2xl p-4 cursor-pointer flex items-start gap-3 {{ $defaultView === $value ? 'border-amber-400 bg-amber-50' : 'border-slate-200' }}">
                                <input type="radio" name="view" value="{{ $value }}" class="mt-1"
                                       {{ $defaultView === $value ? 'checked' : '' }} required>
                                <div>
                                    <p class="font-semibold">{{ $option['label'] }}</p>
                                    <p class="text-xs text-slate-500">Se mostrará en la vista seleccionada.</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-semibold text-slate-700 mb-1">Fecha de inicio</label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required
                               class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 focus:ring-amber-400 focus:border-amber-400">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-semibold text-slate-700 mb-1">Fecha de fin</label>
                        <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required
                               class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 focus:ring-amber-400 focus:border-amber-400">
                    </div>
                </div>

                <div>
                    <label for="repeat_days" class="block text-sm font-semibold text-slate-700 mb-1">Días de la semana</label>
                    <select id="repeat_days" name="repeat_days[]" multiple
                            class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 focus:ring-amber-400 focus:border-amber-400 min-h-[140px]">
                        @foreach($weekDays as $day)
                            <option value="{{ $day['value'] }}" {{ in_array($day['value'], $selectedDays) ? 'selected' : '' }}>
                                {{ $day['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Déjalo vacío para mostrarlo todos los días dentro del rango de fechas.</p>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                    <label for="active" class="block text-sm font-semibold text-slate-700 mb-1">Estado</label>
                    <select id="active" name="active" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 focus:ring-amber-400 focus:border-amber-400">
                        <option value="1" {{ old('active', 1) == 1 ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('active', 1) == 0 ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('admin.new-panel', ['section' => 'popups']) }}" class="px-4 py-2 rounded-full border border-slate-200 text-sm font-semibold text-slate-600 hover:border-slate-400">Cancelar</a>
                    <button type="submit" class="px-6 py-2 rounded-full bg-amber-500 text-white font-semibold hover:bg-amber-600">Guardar pop-up</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
