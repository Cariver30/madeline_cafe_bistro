@php
    $currentUser = auth()->user();
@endphp

@if($currentUser && $currentUser->isAdmin())
    <form action="{{ route('admin.updateBackground') }}" method="POST">
        @csrf
        <div class="border rounded-3 p-3 mb-4">
            <h5 class="mb-2">Integración Clover</h5>
            <p class="text-muted small mb-3">Solo admin. Guarda credenciales y luego mapea categorías.</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="clover_merchant_id" class="form-label">Merchant ID (UID)</label>
                    <input type="text" class="form-control" id="clover_merchant_id" name="clover_merchant_id" value="{{ $settings->clover_merchant_id ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label for="clover_access_token" class="form-label">Access Token (Bearer)</label>
                    <input type="password" class="form-control" id="clover_access_token" name="clover_access_token" placeholder="{{ $settings->clover_access_token ? 'Ya configurado' : '' }}">
                    <small class="text-muted">Deja vacío para mantener el token actual.</small>
                </div>
                <div class="col-md-4">
                    <label for="clover_env" class="form-label">Entorno</label>
                    <select class="form-select" id="clover_env" name="clover_env">
                        <option value="production" @selected(($settings->clover_env ?? 'production') === 'production')>Producción</option>
                        <option value="sandbox" @selected(($settings->clover_env ?? 'production') === 'sandbox')>Sandbox</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="clover_device_host" class="form-label">Clover Device Host (recibos)</label>
                    <input type="text" class="form-control" id="clover_device_host" name="clover_device_host" value="{{ $settings->clover_device_host ?? '' }}" placeholder="https://192.168.x.x:12346">
                    <small class="text-muted">Debe ser accesible desde el servidor donde corre Laravel.</small>
                </div>
                <div class="col-md-6">
                    <label for="clover_device_token" class="form-label">Clover Device Token</label>
                    <input type="password" class="form-control" id="clover_device_token" name="clover_device_token" placeholder="{{ $settings->clover_device_token ? 'Ya configurado' : '' }}">
                    <small class="text-muted">Token del dispositivo para enviar recibos por PRINT/EMAIL.</small>
                </div>
            </div>
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="clover_clear_token" name="clover_clear_token" value="1">
                <label class="form-check-label" for="clover_clear_token">Limpiar token guardado</label>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button class="btn btn-success" type="submit">Guardar credenciales</button>
                <button class="btn btn-outline-secondary" type="submit" formaction="{{ route('admin.clover.test') }}">Probar conexión</button>
                <button class="btn btn-dark" type="submit" formaction="{{ route('admin.clover.sync_all') }}">Sync Clover (categorías + items)</button>
                <a class="btn btn-outline-primary" href="{{ route('admin.clover.index') }}">Mapear categorías</a>
            </div>
            <p class="text-muted small mt-2 mb-0">Nota: el sync de items requiere que las categorías estén mapeadas a una vista.</p>
        </div>
    </form>
@else
    <div class="border rounded-3 p-3">
        <p class="text-muted small mb-0">Esta sección solo está disponible para administradores.</p>
    </div>
@endif
