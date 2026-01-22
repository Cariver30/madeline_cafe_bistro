@php
    $expandCocktailCategories = request('expand') === 'cocktail-categories';
    $cocktailLabel = $cocktailLabel ?? 'Cócteles';
    $cocktailLabelSingular = \Illuminate\Support\Str::singular($cocktailLabel) ?: $cocktailLabel;
    $cocktailLabelLower = \Illuminate\Support\Str::lower($cocktailLabel);
@endphp

<section class="mt-5">
    <h3>{{ $cocktailLabel }}</h3>
    <button class="btn btn-primary mb-3" onclick="toggleVisibility('cocktails-list')">Gestionar {{ $cocktailLabel }}</button>
    <div id="cocktails-list" class="hidden">
        <input type="text" id="searchCocktailsInput" onkeyup="filterCocktails()" placeholder="Buscar {{ $cocktailLabelLower }}..." class="form-control mb-3">
        <a href="{{ route('cocktails.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear nuevo {{ \Illuminate\Support\Str::lower($cocktailLabelSingular) }}</a>
        <div id="cocktailsList">
            @foreach($cocktails as $cocktail)
                <div class="card mb-3 cocktail-item" data-name="{{ $cocktail->name }}" data-category="{{ $cocktail->category?->name ?? 'Sin categoria' }}">
                    <div class="card-body d-flex gap-3 flex-wrap">
                        <div class="flex-shrink-0">
                            <img src="{{ asset('storage/' . $cocktail->image) }}" alt="{{ $cocktail->name }}" class="rounded-3 shadow-sm" style="width: 140px; height: 140px; object-fit: cover;">
                        </div>
                        <div class="flex-fill">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <h3 class="card-title mb-0">{{ $cocktail->name }}</h3>
                                <span class="badge {{ $cocktail->visible ? 'bg-success' : 'bg-secondary' }}">{{ $cocktail->visible ? 'Visible' : 'Oculto' }}</span>
                            </div>
                            <p class="card-text small text-muted mb-2">{{ $cocktail->description }}</p>
                            <p class="card-text fw-semibold">${{ $cocktail->price }}</p>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <a href="{{ route('cocktails.edit', $cocktail) }}" class="btn btn-outline-primary btn-sm">Editar</a>
                                <button class="btn btn-outline-danger btn-sm" form="delete-cocktail-{{ $cocktail->id }}">Eliminar</button>
                                <form method="POST" action="{{ route('cocktails.toggleVisibility', $cocktail) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $cocktail->visible ? 'btn-warning text-dark' : 'btn-success' }}">
                                        {{ $cocktail->visible ? 'Ocultar' : 'Mostrar' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <form id="delete-cocktail-{{ $cocktail->id }}" method="POST" action="{{ route('cocktails.destroy', $cocktail) }}" style="display:none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Categorías -->
<section class="mt-5">
    <h3>Categorías de {{ $cocktailLabel }}</h3>
    <button class="btn btn-primary mb-3" onclick="toggleVisibility('cocktail-categories')">Categorías</button>
    <div id="cocktail-categories" class="{{ $expandCocktailCategories ? '' : 'hidden' }}">
        <a href="{{ route('cocktail-categories.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear nueva categoría de {{ \Illuminate\Support\Str::lower($cocktailLabelSingular) }}</a>
        <div class="card my-3">
            <div class="card-body">
                <p class="text-muted small mb-2">Arrastra para definir el orden en el listado de cócteles.</p>
                <ul class="list-group cocktail-category-sortable">
                    @foreach($cocktailCategories->sortBy('order') as $category)
                        <li class="list-group-item d-flex align-items-center gap-2 sortable-item" data-id="{{ $category->id }}">
                            <span class="text-muted">&#x2630;</span>
                            <span class="flex-fill">{{ $category->name }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div id="cocktailCategoriesList">
            @foreach($cocktailCategories as $category)
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
                                <form method="POST" action="{{ route('cocktail-categories.toggleCover', $category) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $category->show_on_cover ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">
                                        {{ $category->show_on_cover ? 'Quitar de portada' : 'Mostrar en portada' }}
                                    </button>
                                </form>
                                <a href="{{ route('cocktail-categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h4 class="h6 text-muted">Subcategorías</h4>
                            @if($category->subcategories->count())
                                <ul class="list-group mb-3">
                                    @foreach($category->subcategories as $subcategory)
                                        <li class="list-group-item d-flex align-items-center gap-2 flex-wrap">
                                            <form method="POST" action="{{ route('cocktail-subcategories.update', $subcategory) }}" class="d-flex align-items-center gap-2 flex-fill">
                                                @csrf
                                                @method('PATCH')
                                                <input type="text" name="name" class="form-control form-control-sm" value="{{ $subcategory->name }}" required>
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Guardar</button>
                                            </form>
                                            <form method="POST" action="{{ route('cocktail-subcategories.destroy', $subcategory) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted small mb-2">Aún no hay subcategorías.</p>
                            @endif
                            <form method="POST" action="{{ route('cocktail-subcategories.store', $category) }}" class="row g-2 align-items-end">
                                @csrf
                                <div class="col-md-8">
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="Nombre de la subcategoría" required>
                                </div>
                                <div class="col-md-4 text-end">
                                    <button type="submit" class="btn btn-sm btn-success w-100">Agregar</button>
                                </div>
                            </form>
                        </div>
                        @if($category->items->count() > 0)
                            <ul class="list-group cocktail-sortable" data-category="{{ $category->id }}">
                                @foreach($category->items as $item)
                                    <li class="list-group-item d-flex align-items-center gap-2 sortable-item" data-id="{{ $item->id }}">
                                        <span class="text-muted">&#x2630;</span>
                                        <span class="flex-fill">
                                            {{ $item->name }} - ${{ $item->price }}
                                            @if($item->subcategory)
                                                <span class="badge bg-info text-dark ms-2">{{ $item->subcategory->name }}</span>
                                            @endif
                                            @if($item->featured_on_cover)
                                                <span class="badge bg-warning text-dark ms-2">Destacado</span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                            <small class="text-muted">Arrastra para reordenar esta categoría.</small>

                            <form method="POST" action="{{ route('cocktail-categories.featuredItems', $category) }}" class="mt-3">
                                @csrf
                                <p class="text-muted small mb-2">Selecciona los {{ \Illuminate\Support\Str::lower($cocktailLabel) }} destacados para la portada:</p>
                                <div class="row g-2">
                                    @foreach($category->items as $item)
                                        <div class="col-sm-6 col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="featured_items[]" value="{{ $item->id }}" id="cocktail-featured-{{ $item->id }}" {{ $item->featured_on_cover ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="cocktail-featured-{{ $item->id }}">
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
                            <a href="{{ route('cocktail-categories.edit', $category) }}" class="btn btn-outline-primary">Editar</a>
                            <button class="btn btn-outline-danger" form="delete-cocktail-category-{{ $category->id }}">Eliminar</button>
                        </div>
                        <form method="POST" action="{{ route('cocktail-categories.update', $category) }}" class="mt-3 row g-2 align-items-end border rounded-3 p-3 bg-light">
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
                                <input type="checkbox" class="form-check-input" id="cocktail-cover-{{ $category->id }}" name="show_on_cover" value="1" {{ $category->show_on_cover ? 'checked' : '' }}>
                                <label class="form-check-label small" for="cocktail-cover-{{ $category->id }}">Portada</label>
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="submit" class="btn btn-sm btn-primary w-100">Guardar</button>
                            </div>
                        </form>
                        <form id="delete-cocktail-category-{{ $category->id }}" method="POST" action="{{ route('cocktail-categories.destroy', $category) }}" style="display:none;">
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
    function filterCocktails() {
        let input = document.getElementById('searchCocktailsInput');
        let filter = input.value.toUpperCase();
        let cocktailsList = document.getElementById('cocktailsList');
        let cocktailItems = cocktailsList.getElementsByClassName('cocktail-item');

        for (let i = 0; i < cocktailItems.length; i++) {
            let name = cocktailItems[i].getAttribute('data-name');
            if (name.toUpperCase().indexOf(filter) > -1) {
                cocktailItems[i].style.display = "";
            } else {
                cocktailItems[i].style.display = "none";
            }
        }
    }
</script>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.cocktail-category-sortable').forEach(list => {
            new Sortable(list, {
                animation: 150,
                ghostClass: 'bg-light',
                onEnd: evt => {
                    const order = Array.from(evt.to.querySelectorAll('li')).map(item => item.dataset.id);
                    fetch('{{ route('cocktail-categories.reorder') }}', {
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
                            alert('No se pudo guardar el orden de categorías de cócteles.');
                        }
                    })
                    .catch(() => alert('Error de red al guardar el orden de categorías.'));
                }
            });
        });

        document.querySelectorAll('.cocktail-sortable').forEach(list => {
            new Sortable(list, {
                animation: 150,
                ghostClass: 'bg-light',
                onEnd: evt => {
                    const categoryId = evt.to.dataset.category;
                    const order = Array.from(evt.to.querySelectorAll('li')).map(item => item.dataset.id);

                    fetch('{{ route('cocktails.reorder') }}', {
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
                            alert('No se pudo guardar el orden de cócteles.');
                        }
                    })
                    .catch(() => alert('Error de red al guardar el orden.'));
                }
            });
        });
    });
</script>
@endpush
