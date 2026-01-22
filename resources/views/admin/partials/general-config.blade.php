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
        <h5 class="mb-3">Imágenes para los CTA de portada</h5>
        <p class="text-muted small mb-3">Cada botón (Menú, Café, Bebidas, Eventos, Reservas) puede mostrar una imagen. Deja el campo vacío para usar solo texto.</p>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">CTA Menú</label>
                <input type="file" class="form-control" name="cta_image_menu">
                @if($settings->cta_image_menu)
                    <img src="{{ asset('storage/' . $settings->cta_image_menu) }}" class="img-fluid rounded mt-2" alt="CTA Menú">
                @endif
            </div>
            <div class="col-md-4">
                <label class="form-label">CTA Café</label>
                <input type="file" class="form-control" name="cta_image_cafe">
                @if($settings->cta_image_cafe)
                    <img src="{{ asset('storage/' . $settings->cta_image_cafe) }}" class="img-fluid rounded mt-2" alt="CTA Café">
                @endif
            </div>
            <div class="col-md-4">
                <label class="form-label">CTA Bebidas</label>
                <input type="file" class="form-control" name="cta_image_cocktails">
                @if($settings->cta_image_cocktails)
                    <img src="{{ asset('storage/' . $settings->cta_image_cocktails) }}" class="img-fluid rounded mt-2" alt="CTA Bebidas">
                @endif
            </div>
            <div class="col-md-4">
                <label class="form-label">CTA Eventos</label>
                <input type="file" class="form-control" name="cta_image_events">
                @if($settings->cta_image_events)
                    <img src="{{ asset('storage/' . $settings->cta_image_events) }}" class="img-fluid rounded mt-2" alt="CTA Eventos">
                @endif
            </div>
            <div class="col-md-4">
                <label class="form-label">CTA Reservas</label>
                <input type="file" class="form-control" name="cta_image_reservations">
                @if($settings->cta_image_reservations)
                    <img src="{{ asset('storage/' . $settings->cta_image_reservations) }}" class="img-fluid rounded mt-2" alt="CTA Reservas">
                @endif
            </div>
        </div>
    </div>

    <div class="border rounded-3 p-3 mb-4">
        <h5 class="mb-3">Colores individuales de CTA</h5>
        @php
            $ctaKeys = [
                'menu' => 'Menú',
                'cafe' => 'Café & Brunch',
                'cocktails' => 'Bebidas',
                'events' => 'Eventos',
                'reservations' => 'Reservas',
            ];
        @endphp
        <div class="row g-3">
            @foreach($ctaKeys as $key => $label)
                <div class="col-md-6">
                    <label class="form-label">Fondo {{ $label }}</label>
                    <input type="color" class="form-control" name="cover_cta_{{ $key }}_bg_color" value="{{ $settings->{'cover_cta_'.$key.'_bg_color'} ?? '#000000' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Texto {{ $label }}</label>
                    <input type="color" class="form-control" name="cover_cta_{{ $key }}_text_color" value="{{ $settings->{'cover_cta_'.$key.'_text_color'} ?? '#ffffff' }}">
                </div>
            @endforeach
        </div>
        <small class="text-muted d-block mt-2">Si dejas un color vacío usará el fondo genérico configurado arriba.</small>
    </div>

    <div class="border rounded-3 p-3 mb-4">
        <h5 class="mb-3">Visibilidad de CTA principales</h5>
        <p class="text-muted small mb-3">Decide qué botones se muestran en la portada. La tarjeta VIP se controla abajo.</p>
        <div class="row g-3">
            @php
                $ctaVisibility = [
                    'show_cta_menu' => 'Mostrar CTA Menú',
                    'show_cta_cafe' => 'Mostrar CTA Café & Brunch',
                    'show_cta_cocktails' => 'Mostrar CTA Bebidas',
                    'show_cta_events' => 'Mostrar CTA Eventos',
                    'show_cta_reservations' => 'Mostrar CTA Reservas',
                ];
            @endphp
            @foreach($ctaVisibility as $field => $label)
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input class="form-check-input" type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1" {{ ($settings->{$field} ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                    </div>
                </div>
            @endforeach
        </div>
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
                <input type="text" class="form-control" id="cover_location_text" name="cover_location_text" value="{{ $settings->cover_location_text ?? '' }}" placeholder="Café Negro · Miramar">
            </div>
            <div class="col-12">
                <label for="cover_hero_title" class="form-label">Título principal</label>
                <input type="text" class="form-control" id="cover_hero_title" name="cover_hero_title" value="{{ $settings->cover_hero_title ?? '' }}" placeholder="Bienvenido a Café Negro. ...">
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
        <h5 class="mb-3">Etiquetas de botones en la portada</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="button_label_menu" class="form-label">Botón principal 1</label>
                <input type="text" class="form-control" id="button_label_menu" name="button_label_menu" value="{{ $settings->button_label_menu ?? 'Menú' }}">
            </div>
            <div class="col-md-6">
                <label for="button_label_cocktails" class="form-label">Botón principal 2</label>
                <input type="text" class="form-control" id="button_label_cocktails" name="button_label_cocktails" value="{{ $settings->button_label_cocktails ?? 'Cócteles' }}">
            </div>
            <div class="col-md-6">
                <label for="button_label_wines" class="form-label">Botón principal 3</label>
                <input type="text" class="form-control" id="button_label_wines" name="button_label_wines" value="{{ $settings->button_label_wines ?? 'Cafe' }}">
            </div>
            <div class="col-md-6">
                <label for="button_label_events" class="form-label">Botón principal 4</label>
                <input type="text" class="form-control" id="button_label_events" name="button_label_events" value="{{ $settings->button_label_events ?? 'Eventos especiales' }}">
            </div>
            <div class="col-md-6">
                <label for="button_label_vip" class="form-label">Botón lista VIP</label>
                <input type="text" class="form-control" id="button_label_vip" name="button_label_vip" value="{{ $settings->button_label_vip ?? 'Lista VIP' }}">
            </div>
            <div class="col-md-6">
                <label for="button_label_reservations" class="form-label">Botón de reservas</label>
                <input type="text" class="form-control" id="button_label_reservations" name="button_label_reservations" value="{{ $settings->button_label_reservations ?? 'Reservas' }}">
            </div>
        </div>
    </div>

    <div class="border rounded-3 p-3 mb-4">
        <h5 class="mb-3">Nombres de secciones (tabs)</h5>
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
