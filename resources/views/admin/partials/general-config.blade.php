@php
    $currentUser = auth()->user();
    $isManager = $currentUser && $currentUser->isManager();
@endphp

@if($isManager)
    <form action="{{ route('admin.contact-info.update') }}" method="POST" class="space-y-4">
        @csrf
        <div class="border rounded-3 p-3 mb-4">
            <h5 class="mb-2">Contacto y horarios</h5>
            <p class="text-muted small mb-3">Este bloque es el único editable para gerencia.</p>
            <div class="mb-3">
                <label for="phone_number" class="form-label">Número de Teléfono</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ $settings->phone_number ?? '' }}">
            </div>
            <div class="mb-3">
                <label for="business_hours" class="form-label">Horarios de Atención</label>
                <textarea class="form-control" id="business_hours" name="business_hours">{{ $settings->business_hours ?? '' }}</textarea>
            </div>
            <div class="mb-3">
                <label for="cover_location_text" class="form-label">Dirección / ubicación</label>
                <input type="text" class="form-control" id="cover_location_text" name="cover_location_text" value="{{ $settings->cover_location_text ?? '' }}" placeholder="{{ config('app.name', 'Restaurant') }}">
            </div>
            <div class="text-end">
                <button class="btn btn-primary">Guardar contacto</button>
            </div>
        </div>
    </form>
@else
<form action="{{ route('admin.updateBackground') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
        <label for="logo" class="form-label">Logo</label>
        <input type="file" class="form-control" id="logo" name="logo">
        @if($settings && $settings->logo)
            <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" class="img-fluid mt-2">
        @endif
    </div>

    <div class="border rounded-3 p-3 mb-4">
        <h5 class="mb-3">Gestor de CTA de portada</h5>
        <p class="text-muted small mb-3">Configura nombre, acción, visibilidad, orden, imagen y colores desde un solo bloque.</p>
        @php
	            $ctaManager = [
	                'menu' => ['label' => 'Menú', 'label_field' => 'button_label_menu', 'show_field' => 'show_cta_menu', 'image_field' => 'cta_image_menu'],
	                'online' => ['label' => 'Ordenar en línea', 'label_field' => 'button_label_online', 'show_field' => 'show_cta_online', 'image_field' => 'cta_image_online'],
	                'cafe' => ['label' => 'Café & Brunch', 'label_field' => 'button_label_wines', 'show_field' => 'show_cta_cafe', 'image_field' => 'cta_image_cafe'],
	                'cocktails' => ['label' => 'Bebidas', 'label_field' => 'button_label_cocktails', 'show_field' => 'show_cta_cocktails', 'image_field' => 'cta_image_cocktails'],
	                'cantina' => ['label' => 'Cantina', 'label_field' => 'button_label_cantina', 'show_field' => 'show_cta_cantina', 'image_field' => 'cta_image_cantina'],
	                'specials' => ['label' => 'Especiales', 'label_field' => 'button_label_specials', 'show_field' => 'show_cta_specials', 'image_field' => 'cta_image_specials'],
	                'events' => ['label' => 'Eventos', 'label_field' => 'button_label_events', 'show_field' => 'show_cta_events', 'image_field' => 'cta_image_events'],
	                'reservations' => ['label' => 'Reservas', 'label_field' => 'button_label_reservations', 'show_field' => 'show_cta_reservations', 'image_field' => 'cta_image_reservations'],
	                'vip' => ['label' => 'Lista VIP', 'label_field' => 'button_label_vip', 'show_field' => 'show_cta_vip', 'image_field' => null],
	            ];
            $ctaTargetOptions = [
                'menu' => 'Menú',
                'online' => 'Ordenar en línea',
                'cafe' => 'Café & Brunch',
                'cocktails' => 'Bebidas',
                'cantina' => 'Cantina',
                'specials' => 'Especiales',
                'events' => 'Eventos',
                'reservations' => 'Reservas',
                'vip' => 'Lista VIP',
            ];
            $defaultOrder = array_keys($ctaManager);
            $storedOrder = $settings->cover_cta_order ?? [];
            if (is_string($storedOrder)) {
                $decoded = json_decode($storedOrder, true);
                $storedOrder = is_array($decoded) ? $decoded : [];
            }
            $storedOrder = array_values(array_unique(array_filter($storedOrder, fn($key) => in_array($key, $defaultOrder, true))));
            $ordered = array_values(array_merge($storedOrder, array_diff($defaultOrder, $storedOrder)));
            $ctaPositions = [];
            foreach ($ordered as $index => $key) {
                $ctaPositions[$key] = $index + 1;
            }
            $storedTargets = $settings->cover_cta_targets ?? [];
            if (is_string($storedTargets)) {
                $decodedTargets = json_decode($storedTargets, true);
                $storedTargets = is_array($decodedTargets) ? $decodedTargets : [];
            }
        @endphp

        <div class="row g-3">
            @foreach($ctaManager as $key => $meta)
                <div class="col-12">
                    <div class="border rounded-3 p-3">
                        <div class="d-flex flex-wrap gap-3 align-items-end">
                            <div style="min-width: 90px;">
                                <label class="form-label">Orden</label>
                                <input type="number"
                                       class="form-control"
                                       name="cover_cta_position[{{ $key }}]"
                                       min="1"
                                       value="{{ $ctaPositions[$key] ?? '' }}">
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label">Nombre visible</label>
                                <input type="text"
                                       class="form-control"
                                       name="{{ $meta['label_field'] }}"
                                       value="{{ $settings->{$meta['label_field']} ?? $meta['label'] }}">
                            </div>
                            <div style="min-width: 220px;">
                                <label class="form-label">Acción</label>
                                <select class="form-select" name="cover_cta_target[{{ $key }}]">
                                    @foreach($ctaTargetOptions as $targetKey => $targetLabel)
                                        <option value="{{ $targetKey }}" {{ ($storedTargets[$key] ?? $key) === $targetKey ? 'selected' : '' }}>
                                            {{ $targetLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label d-block">Visible</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="{{ $meta['show_field'] }}" value="0">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="show_{{ $key }}"
                                           name="{{ $meta['show_field'] }}"
                                           value="1"
                                           {{ ($settings->{$meta['show_field']} ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <label class="form-label">Imagen</label>
                                @if($meta['image_field'])
                                    <input type="file" class="form-control" name="{{ $meta['image_field'] }}">
                                    @if($settings->{$meta['image_field']})
                                        <img src="{{ asset('storage/' . $settings->{$meta['image_field']}) }}" class="img-fluid rounded mt-2" alt="CTA {{ $meta['label'] }}">
                                    @endif
                                @else
                                    <div class="form-text text-muted">No aplica para este CTA.</div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color de fondo</label>
                                <input type="color" class="form-control" name="cover_cta_{{ $key }}_bg_color" value="{{ $settings->{'cover_cta_'.$key.'_bg_color'} ?? '#000000' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color de texto</label>
                                <input type="color" class="form-control" name="cover_cta_{{ $key }}_text_color" value="{{ $settings->{'cover_cta_'.$key.'_text_color'} ?? '#ffffff' }}">
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <small class="text-muted d-block mt-2">Los cambios se aplican al guardar el formulario.</small>
    </div>

    <div class="mb-3">
        <label for="facebook_url" class="form-label">Facebook URL</label>
        <input type="url" class="form-control" id="facebook_url" name="facebook_url" value="{{ $settings->facebook_url ?? '' }}">
    </div>
    <div class="mb-3">
        <label for="twitter_url" class="form-label">Twitter URL</label>
        <input type="url" class="form-control" id="twitter_url" name="twitter_url" value="{{ $settings->twitter_url ?? '' }}">
    </div>
    <div class="mb-3">
        <label for="instagram_url" class="form-label">Instagram URL</label>
        <input type="url" class="form-control" id="instagram_url" name="instagram_url" value="{{ $settings->instagram_url ?? '' }}">
    </div>
    <div class="mb-3">
        <label for="phone_number" class="form-label">Número de Teléfono</label>
        <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ $settings->phone_number ?? '' }}">
    </div>
    <div class="mb-3">
        <label for="business_hours" class="form-label">Horarios de Atención</label>
        <textarea class="form-control" id="business_hours" name="business_hours">{{ $settings->business_hours ?? '' }}</textarea>
    </div>

    @php
        $onlineSchedule = $settings->online_schedule ?? [];
        $onlinePauseMessage = $settings->online_pause_message ?? 'Por el momento no estamos tomando órdenes en línea.';
        $onlineDays = [
            'mon' => 'Lunes',
            'tue' => 'Martes',
            'wed' => 'Miércoles',
            'thu' => 'Jueves',
            'fri' => 'Viernes',
            'sat' => 'Sábado',
            'sun' => 'Domingo',
        ];
    @endphp
    <div class="border rounded-3 p-3 mb-4">
        <h5 class="mb-2">Ordenar en línea (control)</h5>
        <p class="text-muted small mb-3">Configura si el sistema acepta órdenes online y define horarios por día.</p>
        <div class="form-check form-switch mb-3">
            <input type="hidden" name="online_enabled" value="0">
            <input class="form-check-input" type="checkbox" id="online_enabled" name="online_enabled" value="1" {{ ($settings->online_enabled ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="online_enabled">Ordenar en línea activo</label>
        </div>
        <div class="mb-3">
            <label for="online_pause_message" class="form-label">Mensaje cuando está cerrado</label>
            <input type="text" class="form-control" id="online_pause_message" name="online_pause_message" value="{{ $onlinePauseMessage }}" placeholder="Por el momento no estamos tomando órdenes en línea.">
        </div>
        <div class="row g-3">
            @foreach ($onlineDays as $dayKey => $dayLabel)
                @php
                    $dayConfig = $onlineSchedule[$dayKey] ?? [];
                    $dayClosed = (bool) ($dayConfig['closed'] ?? false);
                    $dayStart = $dayConfig['start'] ?? '';
                    $dayEnd = $dayConfig['end'] ?? '';
                @endphp
                <div class="col-12 col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <strong>{{ $dayLabel }}</strong>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="online_schedule[{{ $dayKey }}][closed]" value="0">
                                <input class="form-check-input" type="checkbox" id="online_schedule_{{ $dayKey }}_closed" name="online_schedule[{{ $dayKey }}][closed]" value="1" {{ $dayClosed ? 'checked' : '' }}>
                                <label class="form-check-label" for="online_schedule_{{ $dayKey }}_closed">Cerrado</label>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small text-muted">Desde</label>
                                <input type="time" class="form-control" name="online_schedule[{{ $dayKey }}][start]" value="{{ $dayStart }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted">Hasta</label>
                                <input type="time" class="form-control" name="online_schedule[{{ $dayKey }}][end]" value="{{ $dayEnd }}">
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @php
        $tipPresets = collect($settings->tip_presets ?? [15, 18, 20])
            ->filter(fn ($value) => is_numeric($value) && $value > 0 && $value <= 100)
            ->values();
    @endphp
    <div class="border rounded-3 p-3 mb-4">
        <h5 class="mb-2">Propinas POS</h5>
        <p class="text-muted small mb-3">Define los porcentajes sugeridos que verán meseros, gerente y cajero.</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="tip_presets" class="form-label">Porcentajes sugeridos</label>
                <input
                    type="text"
                    class="form-control"
                    id="tip_presets"
                    name="tip_presets"
                    value="{{ $tipPresets->implode(', ') }}"
                    placeholder="15, 18, 20">
                <small class="text-muted">Separa con coma. Ej: 15, 18, 20</small>
            </div>
            <div class="col-md-6 d-flex flex-column gap-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="tip_allow_custom" value="0">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="tip_allow_custom"
                        name="tip_allow_custom"
                        value="1"
                        {{ ($settings->tip_allow_custom ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="tip_allow_custom">Permitir propina personalizada</label>
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="tip_allow_skip" value="0">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="tip_allow_skip"
                        name="tip_allow_skip"
                        value="1"
                        {{ ($settings->tip_allow_skip ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="tip_allow_skip">Mostrar boton "Sin propina"</label>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="font_family_cover" class="form-label">Familia de Fuente de Cover</label>
        <input type="text" class="form-control" id="font_family_cover" name="font_family_cover" value="{{ $settings->font_family_cover ?? 'Arial' }}">
    </div>
    <div class="mb-3">
        <label for="text_color_cover" class="form-label">Color del Texto de Cover</label>
        <input type="color" class="form-control" id="text_color_cover" name="text_color_cover" value="{{ $settings->text_color_cover ?? '#000000' }}">
    </div>
    <div class="mb-3">
        <label for="text_color_cover_secondary" class="form-label">Color secundario de textos (párrafos)</label>
        <input type="color" class="form-control" id="text_color_cover_secondary" name="text_color_cover_secondary" value="{{ $settings->text_color_cover_secondary ?? '#bfbfbf' }}">
        <small class="text-muted">Se aplica a descripciones, figcaption y textos largos.</small>
    </div>
    <div class="mb-3">
        <label for="background_image_cover" class="form-label">Imagen de Fondo Cover</label>
        <input type="file" class="form-control" id="background_image_cover" name="background_image_cover">
    </div>
    <div class="mb-3">
        <label for="card_opacity_cover" class="form-label">Opacidad de las Tarjetas de Cover</label>
        <input type="number" step="0.1" class="form-control" id="card_opacity_cover" name="card_opacity_cover" value="{{ $settings->card_opacity_cover ?? 1 }}">
    </div>
    <div class="mb-3">
        <label for="card_bg_color_cover" class="form-label">Color de fondo de tarjetas Cover</label>
        <input type="color" class="form-control" id="card_bg_color_cover" name="card_bg_color_cover" value="{{ $settings->card_bg_color_cover ?? '#000000' }}">
        <small class="text-muted">Se mezcla con la opacidad configurada arriba.</small>
    </div>
    <div class="border rounded-3 p-3 mb-4">
        <h5 class="mb-3">Textos principales del cover</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="cover_hero_kicker" class="form-label">Etiqueta superior (ej. Café · desayuno)</label>
                <input type="text" class="form-control" id="cover_hero_kicker" name="cover_hero_kicker" value="{{ $settings->cover_hero_kicker ?? '' }}">
            </div>
            <div class="col-md-6">
                <label for="cover_location_text" class="form-label">Texto de ubicación</label>
                <input type="text" class="form-control" id="cover_location_text" name="cover_location_text" value="{{ $settings->cover_location_text ?? '' }}" placeholder="{{ config('app.name', 'Restaurant') }}">
            </div>
            <div class="col-12">
                <label for="cover_hero_title" class="form-label">Título principal</label>
                <input type="text" class="form-control" id="cover_hero_title" name="cover_hero_title" value="{{ $settings->cover_hero_title ?? '' }}" placeholder="Bienvenido. ...">
            </div>
            <div class="col-12">
                <label for="cover_hero_paragraph" class="form-label">Descripción</label>
                <textarea class="form-control" id="cover_hero_paragraph" name="cover_hero_paragraph" rows="3">{{ $settings->cover_hero_paragraph ?? '' }}</textarea>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <label for="button_color_cover" class="form-label">Color del Botón de Cover</label>
        <input type="color" class="form-control" id="button_color_cover" name="button_color_cover" value="{{ $settings->button_color_cover ?? '#000000' }}">
    </div>
    <div class="mb-3">
        <label for="button_font_size_cover" class="form-label">Tamaño de la Fuente del Botón de Cover</label>
        <input type="number" class="form-control" id="button_font_size_cover" name="button_font_size_cover" value="{{ $settings->button_font_size_cover ?? 18 }}">
    </div>

    <div class="border rounded-3 p-3 mb-4">
        <h5 class="mb-2">Pestañas del menú (App + Web)</h5>
        <p class="text-muted small mb-3">Estas pestañas son las vistas que verá el gerente/mesero/host en la app y el cliente en la web. Solo el admin decide cuáles se habilitan.</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="tab_label_menu" class="form-label">Nombre para “Menú”</label>
                <input type="text" class="form-control" id="tab_label_menu" name="tab_label_menu" value="{{ $settings->tab_label_menu ?? 'Menú' }}">
            </div>
            <div class="col-md-6">
                <label for="tab_label_cocktails" class="form-label">Nombre para “{{ $settings->button_label_cocktails ?? 'Cócteles' }}”</label>
                <input type="text" class="form-control" id="tab_label_cocktails" name="tab_label_cocktails" value="{{ $settings->tab_label_cocktails ?? $settings->button_label_cocktails ?? 'Cócteles' }}">
            </div>
            <div class="col-md-6">
                <label for="tab_label_wines" class="form-label">Nombre para “Café &amp; Brunch”</label>
                <input type="text" class="form-control" id="tab_label_wines" name="tab_label_wines" value="{{ $settings->tab_label_wines ?? 'Café & Brunch' }}">
            </div>
            <div class="col-md-6">
                <label for="tab_label_cantina" class="form-label">Nombre para “Cantina”</label>
                <input type="text" class="form-control" id="tab_label_cantina" name="tab_label_cantina" value="{{ $settings->tab_label_cantina ?? $settings->button_label_cantina ?? 'Cantina' }}">
            </div>
            <div class="col-md-6">
                <label for="tab_label_events" class="form-label">Nombre para “Eventos”</label>
                <input type="text" class="form-control" id="tab_label_events" name="tab_label_events" value="{{ $settings->tab_label_events ?? 'Eventos' }}">
            </div>
            <div class="col-md-6">
                <label for="tab_label_loyalty" class="form-label">Nombre para “Fidelidad”</label>
                <input type="text" class="form-control" id="tab_label_loyalty" name="tab_label_loyalty" value="{{ $settings->tab_label_loyalty ?? 'Fidelidad' }}">
            </div>
        </div>
        <div class="row g-3 mt-3">
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input type="hidden" name="show_tab_menu" value="0">
                    <input class="form-check-input" type="checkbox" id="show_tab_menu" name="show_tab_menu" value="1" {{ $settings->show_tab_menu ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_tab_menu">Mostrar Menú</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input type="hidden" name="show_tab_cocktails" value="0">
                    <input class="form-check-input" type="checkbox" id="show_tab_cocktails" name="show_tab_cocktails" value="1" {{ $settings->show_tab_cocktails ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_tab_cocktails">Mostrar Bebidas</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input type="hidden" name="show_tab_wines" value="0">
                    <input class="form-check-input" type="checkbox" id="show_tab_wines" name="show_tab_wines" value="1" {{ $settings->show_tab_wines ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_tab_wines">Mostrar Café &amp; Brunch</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input type="hidden" name="show_tab_cantina" value="0">
                    <input class="form-check-input" type="checkbox" id="show_tab_cantina" name="show_tab_cantina" value="1" {{ ($settings->show_tab_cantina ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_tab_cantina">Mostrar Cantina</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch mt-2">
                    <input type="hidden" name="show_tab_events" value="0">
                    <input class="form-check-input" type="checkbox" id="show_tab_events" name="show_tab_events" value="1" {{ $settings->show_tab_events ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_tab_events">Mostrar Eventos</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch mt-2">
                    <input type="hidden" name="show_tab_campaigns" value="0">
                    <input class="form-check-input" type="checkbox" id="show_tab_campaigns" name="show_tab_campaigns" value="1" {{ $settings->show_tab_campaigns ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_tab_campaigns">Mostrar Campañas</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch mt-2">
                    <input type="hidden" name="show_tab_popups" value="0">
                    <input class="form-check-input" type="checkbox" id="show_tab_popups" name="show_tab_popups" value="1" {{ $settings->show_tab_popups ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_tab_popups">Mostrar Pop-ups</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch mt-2">
                    <input type="hidden" name="show_tab_loyalty" value="0">
                    <input class="form-check-input" type="checkbox" id="show_tab_loyalty" name="show_tab_loyalty" value="1" {{ $settings->show_tab_loyalty ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_tab_loyalty">Mostrar Fidelidad</label>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <label for="fixed_bottom_font_size" class="form-label">Tamaño de la Fuente de la Información Fija</label>
        <input type="number" class="form-control" id="fixed_bottom_font_size" name="fixed_bottom_font_size" value="{{ $settings->fixed_bottom_font_size ?? 14 }}">
    </div>
    <div class="mb-3">
        <label for="fixed_bottom_font_color" class="form-label">Color de la Fuente de la Información Fija</label>
        <input type="color" class="form-control" id="fixed_bottom_font_color" name="fixed_bottom_font_color" value="{{ $settings->fixed_bottom_font_color ?? '#000000' }}">
    </div>

    <div class="border rounded-3 p-3 mb-4">
        <h5 class="mb-3">Colores para “Lo más vendido”</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="featured_card_bg_color">Fondo de la tarjeta</label>
                <input type="color" class="form-control" id="featured_card_bg_color" name="featured_card_bg_color" value="{{ $settings->featured_card_bg_color ?? '#0f172a' }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="featured_card_text_color">Texto de la tarjeta</label>
                <input type="color" class="form-control" id="featured_card_text_color" name="featured_card_text_color" value="{{ $settings->featured_card_text_color ?? '#ffffff' }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="featured_tab_bg_color">Fondo de pestañas activas</label>
                <input type="color" class="form-control" id="featured_tab_bg_color" name="featured_tab_bg_color" value="{{ $settings->featured_tab_bg_color ?? '#ffffff' }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="featured_tab_text_color">Texto/borde de pestañas</label>
                <input type="color" class="form-control" id="featured_tab_text_color" name="featured_tab_text_color" value="{{ $settings->featured_tab_text_color ?? '#ffffff' }}">
            </div>
        </div>
    </div>

    <div class="border rounded-3 p-3 mb-4">
        <h5 class="mb-3">Control de Lista VIP</h5>
        <div class="form-check form-switch mb-3">
            <input type="hidden" name="show_cta_vip" value="0">
            <input class="form-check-input" type="checkbox" id="show_cta_vip" name="show_cta_vip" value="1" {{ ($settings->show_cta_vip ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="show_cta_vip">Mostrar tarjeta VIP en la portada</label>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="cover_cta_vip_bg_color">Color de fondo</label>
                <input type="color" class="form-control" id="cover_cta_vip_bg_color" name="cover_cta_vip_bg_color" value="{{ $settings->cover_cta_vip_bg_color ?? '#0f172a' }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="cover_cta_vip_text_color">Color de texto</label>
                <input type="color" class="form-control" id="cover_cta_vip_text_color" name="cover_cta_vip_text_color" value="{{ $settings->cover_cta_vip_text_color ?? '#ffffff' }}">
            </div>
        </div>
    </div>

    <div class="border rounded-3 p-3 mb-4">
        <h5 class="mb-2">Seguridad app móvil</h5>
        <p class="text-muted small mb-3">La restricción por IP aplica solo a meseros y hosts. Los gerentes siempre tienen acceso.</p>
        <div class="form-check form-switch mb-3">
            <input type="hidden" name="mobile_ip_restriction_enabled" value="0">
            <input class="form-check-input" type="checkbox" id="mobile_ip_restriction_enabled" name="mobile_ip_restriction_enabled" value="1" {{ ($settings->mobile_ip_restriction_enabled ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="mobile_ip_restriction_enabled">Activar restricción por IP</label>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="mobile_ip_allowlist">IP(s) permitidas</label>
                <textarea class="form-control" id="mobile_ip_allowlist" name="mobile_ip_allowlist" rows="3" placeholder="Ej: 38.10.20.30, 38.10.20.0/24">{{ $settings->mobile_ip_allowlist ?? '' }}</textarea>
                <div class="form-text">Separa con comas o saltos de línea. Acepta rangos CIDR.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="mobile_ip_bypass_emails">Emails con bypass</label>
                <textarea class="form-control" id="mobile_ip_bypass_emails" name="mobile_ip_bypass_emails" rows="3" placeholder="Ej: info@bbtspr.com">{{ $settings->mobile_ip_bypass_emails ?? '' }}</textarea>
                <div class="form-text">Usuarios con estos correos podrán acceder aunque no estén en la IP permitida.</div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
</form>

@if(auth()->user()?->isAdmin())
    <div class="border rounded-3 p-3 mt-4">
        <h5 class="mb-3">Gerentes (solo admin)</h5>
        <form method="POST" action="{{ route('admin.managers.store') }}" class="row g-3 align-items-end mb-4">
            @csrf
            <div class="col-md-4">
                <label class="form-label text-muted small">Nombre</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Correo</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small d-block">Invitación</label>
                <p class="text-muted small mb-2">Se enviará un correo para que cree su contraseña.</p>
                <button class="btn btn-dark w-100">Invitar gerente</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Invitación</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($managers as $manager)
                        <tr>
                            <td>{{ $manager->name }}</td>
                            <td>{{ $manager->email }}</td>
                            <td>{{ $manager->email }}</td>
                            <td>
                                @if($manager->invitation_accepted_at)
                                    <span class="badge text-bg-success">Activada {{ optional($manager->invitation_accepted_at)->diffForHumans() }}</span>
                                @elseif($manager->invitation_token)
                                    <span class="badge text-bg-warning text-dark">
                                        Pendiente {{ optional($manager->invitation_sent_at)->diffForHumans() ?? '' }}
                                    </span>
                                @else
                                    <span class="badge text-bg-secondary">Manual</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $manager->active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $manager->active ? 'Activo' : 'Bloqueado' }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    @if($manager->invitation_token)
                                        <form method="POST" action="{{ route('admin.managers.resend', $manager) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-secondary">
                                                Reenviar invitación
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.managers.toggle', $manager) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm {{ $manager->active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                            {{ $manager->active ? 'Bloquear' : 'Reactivar' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.managers.destroy', $manager) }}" onsubmit="return confirm('¿Eliminar gerente?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Aún no has creado gerentes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@endif
