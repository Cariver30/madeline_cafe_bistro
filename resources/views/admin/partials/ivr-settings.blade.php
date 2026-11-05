@php
    $ivrToggles = [
        'ivr_option_hours' => '1 · Horario (SMS)',
        'ivr_option_menu' => '2 · Menú (SMS)',
        'ivr_option_location' => '3 · Ubicación (SMS)',
        'ivr_option_order' => '4 · Sala de actividades / ordenar en línea (SMS)',
        'ivr_option_transfer' => '5 · Hablar con el personal (transferir llamada)',
    ];
@endphp
<form action="{{ route('admin.ivr-settings.update') }}" method="POST">
    @csrf
    <div class="border rounded-3 p-3 mb-4">
        <h5 class="mb-2">Teléfono automático (IVR)</h5>
        <p class="text-muted small mb-3">Si apagas el IVR, las llamadas pasan directo al número de transferencia, sin menú.</p>
        <div class="form-check form-switch mb-3">
            <input type="hidden" name="ivr_enabled" value="0">
            <input class="form-check-input" type="checkbox" id="ivr_enabled" name="ivr_enabled" value="1" {{ ($settings->ivr_enabled ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="ivr_enabled">IVR activo</label>
        </div>
        <p class="fw-semibold small mb-2">Opciones del menú</p>
        @foreach ($ivrToggles as $toggle => $label)
            <div class="form-check form-switch mb-2">
                <input type="hidden" name="{{ $toggle }}" value="0">
                <input class="form-check-input" type="checkbox" id="{{ $toggle }}" name="{{ $toggle }}" value="1" {{ ($settings->{$toggle} ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $toggle }}">{{ $label }}</label>
            </div>
        @endforeach
        <div class="my-3">
            <label for="ivr_transfer_number" class="form-label">Número de transferencia</label>
            <input type="text" class="form-control" id="ivr_transfer_number" name="ivr_transfer_number" value="{{ old('ivr_transfer_number', $settings->ivr_transfer_number ?? '') }}" placeholder="{{ config('ivr.transfer_to') }}">
            @error('ivr_transfer_number')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="ivr_hours_es" class="form-label">Horario por SMS (español)</label>
            <textarea class="form-control" id="ivr_hours_es" name="ivr_hours_es" rows="6">{{ old('ivr_hours_es', ($settings->ivr_hours_es ?? null) ?: config('ivr.hours.es')) }}</textarea>
        </div>
        <div class="mb-3">
            <label for="ivr_hours_en" class="form-label">Horario por SMS (inglés)</label>
            <textarea class="form-control" id="ivr_hours_en" name="ivr_hours_en" rows="6">{{ old('ivr_hours_en', ($settings->ivr_hours_en ?? null) ?: config('ivr.hours.en')) }}</textarea>
        </div>
        <div class="text-end">
            <button class="btn btn-primary">Guardar IVR</button>
        </div>
    </div>
</form>
