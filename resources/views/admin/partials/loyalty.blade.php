<div class="inner-panel space-y-6" id="loyalty-settings">
    <div>
        <h3 class="inner-title">Programa de fidelidad</h3>
        <p class="inner-text">Define puntos por visita, crea recompensas y gestiona los accesos de meseros.</p>
    </div>

    <section class="feature-card">
        <h4 class="text-lg font-semibold text-slate-900 mb-2">Parámetros generales</h4>
        <form method="POST" action="{{ route('admin.loyalty.settings') }}" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label text-muted small text-uppercase">Puntos por visita</label>
                <input type="number" name="loyalty_points_per_visit" class="form-control form-control-lg" value="{{ optional($settings)->loyalty_points_per_visit ?? 10 }}" min="1">
            </div>
            <div class="col-md-8">
                <label class="form-label text-muted small text-uppercase">Texto legal o instrucciones</label>
                <textarea name="loyalty_terms" class="form-control" rows="2">{{ optional($settings)->loyalty_terms }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label text-muted small text-uppercase">Mensaje de correo cuando alcance una recompensa</label>
                <textarea name="loyalty_email_copy" class="form-control" rows="3">{{ optional($settings)->loyalty_email_copy }}</textarea>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-primary">Guardar Configuración</button>
            </div>
        </form>
    </section>

    <section class="feature-card">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h4 class="text-lg font-semibold text-slate-900 mb-1">Personal con acceso</h4>
                <p class="text-muted small">Crea usuarios con rol de mesero, host o POS y envíales la invitación para activar su cuenta.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.loyalty.servers.store') }}" class="row g-3 align-items-end mb-4">
            @csrf
            <div class="col-md-3">
                <label class="form-label text-muted small">Nombre</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Correo</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">Rol</label>
                <select name="role" class="form-select" required>
                    <option value="server">Mesero</option>
                    <option value="host">Host</option>
                    <option value="pos">POS</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-dark w-100">Invitar</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Última invitación</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffUsers as $staff)
                        <tr>
                            <td>{{ $staff->name }}</td>
                            <td>{{ $staff->email }}</td>
                            <td>
                                @if($staff->role === 'pos')
                                    POS
                                @elseif($staff->role === 'host')
                                    Host
                                @else
                                    Mesero
                                @endif
                            </td>
                            <td>{{ optional($staff->invitation_sent_at)->diffForHumans() ?? 'N/A' }}</td>
                            <td class="text-center">
                                @php
                                    $statusLabel = $staff->invitation_token
                                        ? 'Pendiente de activar'
                                        : ($staff->active ? 'Activo' : 'Bloqueado');
                                    $badgeClass = $staff->invitation_token
                                        ? 'text-bg-warning'
                                        : ($staff->active ? 'text-bg-success' : 'text-bg-secondary');
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    <form method="POST" action="{{ route('admin.loyalty.servers.resend', $staff) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary">Reenviar invitación</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.loyalty.servers.toggle', $staff) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm {{ $staff->active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                            {{ $staff->active ? 'Bloquear' : 'Reactivar' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.loyalty.servers.destroy', $staff) }}" onsubmit="return confirm('Eliminar este usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Aún no hay personal registrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="feature-card">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h4 class="text-lg font-semibold text-slate-900 mb-1">Recompensas</h4>
                <p class="text-muted small">Define qué beneficios puede redimir un invitado y cuántos puntos necesita.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.loyalty.rewards.store') }}" class="row g-3 align-items-end mb-4">
            @csrf
            <div class="col-md-3">
                <label class="form-label text-muted small">Nombre</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">Descripción</label>
                <input type="text" name="description" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small">Puntos</label>
                <input type="number" name="points_required" min="1" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small">Expira en</label>
                <select name="expiration_days" class="form-select">
                    <option value="">Sin expiración</option>
                    <option value="30">30 días</option>
                    <option value="60">60 días</option>
                    <option value="90">90 días</option>
                    <option value="120">120 días</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-dark w-100">Añadir</button>
            </div>
        </form>

        <div class="row g-3">
            @forelse($loyaltyRewards as $reward)
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100 d-flex flex-column gap-2">
                        <form method="POST" action="{{ route('admin.loyalty.rewards.update', $reward) }}" class="d-flex flex-column gap-2 flex-grow-1">
                            @csrf
                            @method('PUT')
                            <input type="text" name="title" value="{{ $reward->title }}" class="form-control form-control-sm" required>
                            <textarea name="description" rows="2" class="form-control form-control-sm" placeholder="Descripción">{{ $reward->description }}</textarea>
                            @if(!empty($reward->expiration_days))
                                <p class="text-muted small mb-0">Expira {{ (int) $reward->expiration_days }} días después del desbloqueo.</p>
                            @endif
                            <div class="d-flex align-items-center gap-2">
                                <input type="number" name="points_required" value="{{ $reward->points_required }}" class="form-control form-control-sm" min="1" required>
                                <select name="expiration_days" class="form-select form-select-sm">
                                    <option value="" {{ empty($reward->expiration_days) ? 'selected' : '' }}>Sin expiración</option>
                                    <option value="30" {{ (int) $reward->expiration_days === 30 ? 'selected' : '' }}>30 días</option>
                                    <option value="60" {{ (int) $reward->expiration_days === 60 ? 'selected' : '' }}>60 días</option>
                                    <option value="90" {{ (int) $reward->expiration_days === 90 ? 'selected' : '' }}>90 días</option>
                                    <option value="120" {{ (int) $reward->expiration_days === 120 ? 'selected' : '' }}>120 días</option>
                                </select>
                                <div class="form-check ms-3">
                                    <input type="checkbox" name="active" value="1" class="form-check-input" {{ $reward->active ? 'checked' : '' }}>
                                    <label class="form-check-label small">Activa</label>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-primary w-100">Guardar</button>
                        </form>
                        <form method="POST" action="{{ route('admin.loyalty.rewards.destroy', $reward) }}" onsubmit="return confirm('¿Eliminar recompensa?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger w-100">Eliminar</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-muted text-sm">Aún no hay recompensas configuradas.</p>
            @endforelse
        </div>
    </section>

    <section class="feature-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="text-lg font-semibold text-slate-900 mb-1">Clientes destacados</h4>
                    <p class="text-muted small">Los últimos perfiles con más puntos acumulados.</p>
                </div>
                <div class="text-muted small text-end">
                    <div>Registrados: <strong>{{ $loyaltyCustomerCount ?? $loyaltyCustomers->count() }}</strong></div>
                </div>
            </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Puntos</th>
                        <th>Fase</th>
                        <th>Última visita</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loyaltyCustomers as $customer)
                        <tr>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->email }}</td>
                            <td><span class="badge text-bg-primary">{{ $customer->points }}</span></td>
                            <td class="text-muted small">
                                @php
                                    $activeRewards = $loyaltyRewards->where('active', true)->sortBy('points_required');
                                    $nextReward = $activeRewards->first(fn ($reward) => $reward->points_required > $customer->points);
                                    $currentReward = $activeRewards->filter(fn ($reward) => $reward->points_required <= $customer->points)->last();
                                @endphp
                                @if($currentReward)
                                    <div>Desbloqueó: {{ $currentReward->title }} ({{ $currentReward->points_required }} pts)</div>
                                @endif
                                @if($nextReward)
                                    <div>Siguiente: {{ $nextReward->title }} ({{ $nextReward->points_required }} pts)</div>
                                    <div>Faltan {{ max(0, $nextReward->points_required - $customer->points) }} pts</div>
                                @else
                                    <div>Máxima recompensa alcanzada.</div>
                                @endif
                            </td>
                            <td>{{ optional($customer->last_visit_at)->diffForHumans() ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted small">Todavía no hay visitas confirmadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
