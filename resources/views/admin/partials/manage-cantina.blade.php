@php
    $expandCantinaCategories = request('expand') === 'cantina-categories';
    $cantinaLabel = $cantinaLabel ?? 'Cantina';
    $cantinaLabelSingular = \Illuminate\Support\Str::singular($cantinaLabel) ?: $cantinaLabel;
    $cantinaLabelLower = \Illuminate\Support\Str::lower($cantinaLabel);
@endphp

<section class="mt-5">
    <h3>{{ $cantinaLabel }}</h3>
    <button class="btn btn-primary mb-3" onclick="toggleVisibility('cantina-list')">Gestionar {{ $cantinaLabel }}</button>
    <div id="cantina-list" class="hidden">
        <input type="text" id="searchCantinaInput" onkeyup="filterCantinaItems()" placeholder="Buscar {{ $cantinaLabelLower }}..." class="form-control mb-3">
        <a href="{{ route('cantina-items.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear nuevo {{ \Illuminate\Support\Str::lower($cantinaLabelSingular) }}</a>
        <div id="cantinaItemsList">
            @foreach($cantinaItems as $item)
                <div class="card mb-3 cantina-item" data-name="{{ $item->name }}" data-category="{{ $item->category?->name ?? 'Sin categoria' }}">
                    <div class="card-body d-flex gap-3 flex-wrap">
                        <div class="flex-shrink-0">
                            @if($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="rounded-3 shadow-sm" style="width: 140px; height: 140px; object-fit: cover;">
                            @else
                                <div class="rounded-3 bg-light d-flex align-items-center justify-content-center" style="width: 140px; height: 140px;">
                                    <span class="text-muted small">Sin imagen</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-fill">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <h3 class="card-title mb-0">{{ $item->name }}</h3>
                                <span class="badge {{ $item->visible ? 'bg-success' : 'bg-secondary' }}">{{ $item->visible ? 'Visible' : 'Oculto' }}</span>
                            </div>
                            @if($item->description)
                                <p class="card-text small text-muted mb-2">{{ $item->description }}</p>
                            @endif
                            <p class="card-text fw-semibold">${{ $item->price }}</p>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <a href="{{ route('cantina-items.edit', $item) }}" class="btn btn-outline-primary btn-sm">Editar</a>
                                <button class="btn btn-outline-danger btn-sm" form="delete-cantina-item-{{ $item->id }}">Eliminar</button>
                                <form method="POST" action="{{ route('cantina-items.toggleVisibility', $item) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $item->visible ? 'btn-warning text-dark' : 'btn-success' }}">
                                        {{ $item->visible ? 'Ocultar' : 'Mostrar' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <form id="delete-cantina-item-{{ $item->id }}" method="POST" action="{{ route('cantina-items.destroy', $item) }}" style="display:none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="mt-5">
    <h3>Categorías de {{ $cantinaLabel }}</h3>
    <button class="btn btn-primary mb-3" onclick="toggleVisibility('cantina-categories')">Categorías</button>
    <div id="cantina-categories" class="{{ $expandCantinaCategories ? '' : 'hidden' }}">
        <a href="{{ route('cantina-categories.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear nueva categoría de {{ \Illuminate\Support\Str::lower($cantinaLabelSingular) }}</a>
        <div class="card my-3">
            <div class="card-body">
                <p class="text-muted small mb-2">Arrastra para definir el orden en el listado.</p>
                <ul class="list-group cantina-category-sortable">
                    @foreach($cantinaCategories->sortBy('order') as $category)
                        <li class="list-group-item d-flex align-items-center gap-2 sortable-item" data-id="{{ $category->id }}">
                            <span class="text-muted">&#x2630;</span>
                            <span class="flex-fill">{{ $category->name }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div id="cantinaCategoriesList">
            @foreach($cantinaCategories as $category)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap">
                            <h3 class="card-title mb-0">{{ $category->name }}</h3>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small d-flex align-items-center gap-1">
                                    <i class="fas fa-grip-vertical"></i> Arrastra
                                </span>
                                @if($category->show_on_cover)
                                    <span class="badge bg-warning text-dark">En portada</span>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('cantina-categories.toggleCover', $category) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $category->show_on_cover ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">
                                        {{ $category->show_on_cover ? 'Quitar de portada' : 'Mostrar en portada' }}
                                    </button>
                                </form>
                                <a href="{{ route('cantina-categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            </div>
                        </div>
                        @if($category->items->count() > 0)
                            <ul class="list-group cantina-sortable" data-category="{{ $category->id }}">
                                @foreach($category->items as $item)
                                    <li class="list-group-item d-flex align-items-center gap-2 sortable-item" data-id="{{ $item->id }}">
                                        <span class="text-muted">&#x2630;</span>
                                        <span class="flex-fill">
                                            {{ $item->name }} - ${{ $item->price }}
                                            @if($item->featured_on_cover)
                                                <span class="badge bg-warning text-dark ms-2">Destacado</span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                            <small class="text-muted">Arrastra para reordenar esta categoría.</small>

                            <form method="POST" action="{{ route('cantina-categories.featuredItems', $category) }}" class="mt-3">
                                @csrf
                                <p class="text-muted small mb-2">Selecciona los {{ \Illuminate\Support\Str::lower($cantinaLabel) }} destacados para la portada:</p>
                                <div class="row g-2">
                                    @foreach($category->items as $item)
                                        <div class="col-sm-6 col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="featured_items[]" value="{{ $item->id }}" id="cantina-featured-{{ $item->id }}" {{ $item->featured_on_cover ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="cantina-featured-{{ $item->id }}">
                                                    {{ $item->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary mt-3">Guardar destacados</button>
                            </form>
                        @else
                            <p>No items found for this category.</p>
                        @endif
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <a href="{{ route('cantina-categories.edit', $category) }}" class="btn btn-outline-primary">Editar</a>
                            <button class="btn btn-outline-danger" form="delete-cantina-category-{{ $category->id }}">Eliminar</button>
                        </div>
                        <form method="POST" action="{{ route('cantina-categories.update', $category) }}" class="mt-3 row g-2 align-items-end border rounded-3 p-3 bg-light">
                            @csrf
                            @method('PUT')
                            <div class="col-md-4">
                                <label class="form-label text-muted small mb-1">Nombre</label>
                                <input type="text" name="name" class="form-control form-control-sm" value="{{ $category->name }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small mb-1">Nombre público</label>
                                <input type="text" name="cover_title" class="form-control form-control-sm" value="{{ $category->cover_title }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small mb-1">Descripción breve</label>
                                <input type="text" name="cover_subtitle" class="form-control form-control-sm" value="{{ $category->cover_subtitle }}">
                            </div>
                            <div class="col-md-1 form-check mt-4">
                                <input type="checkbox" class="form-check-input" id="cantina-cover-{{ $category->id }}" name="show_on_cover" value="1" {{ $category->show_on_cover ? 'checked' : '' }}>
                                <label class="form-check-label small" for="cantina-cover-{{ $category->id }}">Portada</label>
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="submit" class="btn btn-sm btn-primary w-100">Guardar</button>
                            </div>
                        </form>
                        <form id="delete-cantina-category-{{ $category->id }}" method="POST" action="{{ route('cantina-categories.destroy', $category) }}" style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<script>
    function filterCantinaItems() {
        let input = document.getElementById('searchCantinaInput');
        let filter = input.value.toUpperCase();
        let itemsList = document.getElementById('cantinaItemsList');
        let items = itemsList.getElementsByClassName('cantina-item');

        for (let i = 0; i < items.length; i++) {
            let name = items[i].getAttribute('data-name');
            if (name.toUpperCase().indexOf(filter) > -1) {
                items[i].style.display = "";
            } else {
                items[i].style.display = "none";
            }
        }
    }
</script>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.cantina-category-sortable').forEach(list => {
            new Sortable(list, {
                animation: 150,
                ghostClass: 'bg-light',
                onEnd: evt => {
                    const order = Array.from(evt.to.querySelectorAll('li')).map(item => item.dataset.id);
                    fetch('{{ route('cantina-categories.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ order }),
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP '+response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            alert('No se pudo guardar el orden de categorías de cantina.');
                        }
                    })
                    .catch(() => alert('Error de red al guardar el orden de categorías.'));
                }
            });
        });

        document.querySelectorAll('.cantina-sortable').forEach(list => {
            new Sortable(list, {
                animation: 150,
                ghostClass: 'bg-light',
                onEnd: evt => {
                    const categoryId = evt.to.dataset.category;
                    const order = Array.from(evt.to.querySelectorAll('li')).map(item => item.dataset.id);

                    fetch('{{ route('cantina.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ category_id: categoryId, order }),
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP '+response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            alert('No se pudo guardar el orden de cantina.');
                        }
                    })
                    .catch(() => alert('Error de red al guardar el orden.'));
                }
            });
        });
    });
</script>
@endpush
