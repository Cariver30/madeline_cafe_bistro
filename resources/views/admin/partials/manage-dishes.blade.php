<!-- resources/views/admin/partials/manage-dishes.blade.php -->

@php $expandDishCategories = request('expand') === 'dish-categories'; @endphp

<section class="mt-5">
    <h3>Platos</h3>
    <button class="btn btn-primary mb-3" onclick="toggleVisibility('dishes-list')">Gestionar Platos</button>
    <div id="dishes-list" class="hidden">
        <input type="text" id="searchDishesInput" onkeyup="filterDishes()" placeholder="Buscar platos..." class="form-control mb-3">
        <a href="{{ route('dishes.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear nuevo plato</a>
        <div id="dishesList">
            @foreach($dishes as $dish)
                <div class="card mb-3 dish-item" data-name="{{ $dish->name }}" data-category="{{ $dish->category->name }}">
                    <div class="card-body d-flex gap-3 flex-wrap">
                        <div class="flex-shrink-0">
                            <img src="{{ asset('storage/' . $dish->image) }}" alt="{{ $dish->name }}" class="rounded-3 shadow-sm" style="width: 140px; height: 140px; object-fit: cover;">
                        </div>
                        <div class="flex-fill">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <h3 class="card-title mb-0">{{ $dish->name }}</h3>
                                <span class="badge {{ $dish->visible ? 'bg-success' : 'bg-secondary' }}">{{ $dish->visible ? 'Visible' : 'Oculto' }}</span>
                            </div>
                            <p class="card-text small text-muted mb-2">{{ $dish->description }}</p>
                            <p class="card-text fw-semibold">${{ $dish->price }}</p>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <a href="{{ route('dishes.edit', $dish) }}" class="btn btn-outline-primary btn-sm">Editar</a>
                                <button class="btn btn-outline-danger btn-sm" form="delete-dish-{{ $dish->id }}">Eliminar</button>
                                <form method="POST" action="{{ route('dishes.toggleVisibility', $dish) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $dish->visible ? 'btn-warning text-dark' : 'btn-success' }}">
                                        {{ $dish->visible ? 'Ocultar' : 'Mostrar' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <form id="delete-dish-{{ $dish->id }}" method="POST" action="{{ route('dishes.destroy', $dish) }}" style="display:none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="mt-5">
    <h3>Categorías de Platos</h3>
    <button class="btn btn-primary mb-3" onclick="toggleVisibility('dish-categories')">Categorías</button>
    <div id="dish-categories" class="{{ $expandDishCategories ? '' : 'hidden' }}">
        <a href="{{ route('categories.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear nueva categoría</a>
        <div class="card my-3">
            <div class="card-body">
                <p class="text-muted small mb-2">Arrastra las categorías para reorganizar cómo aparecen en el menú.</p>
                <ul class="list-group category-sortable">
                    @foreach($categories->sortBy('order') as $category)
                        <li class="list-group-item d-flex align-items-center gap-2 sortable-item" data-id="{{ $category->id }}">
                            <span class="text-muted sortable-handle">&#x2630;</span>
                            <span class="flex-fill">{{ $category->name }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div id="categoriesList" class="category-card-sortable">
            @foreach($categories as $category)
                <div class="card mb-3 category-card sortable-item" data-id="{{ $category->id }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap">
                            <h3 class="card-title mb-0">{{ $category->name }}</h3>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small d-flex align-items-center gap-1 sortable-handle">
                                    <i class="fas fa-grip-vertical"></i> Arrastra
                                </span>
                                @if($category->show_on_cover)
                                    <span class="badge bg-warning text-dark">En portada</span>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('categories.toggleCover', $category) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $category->show_on_cover ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">
                                        {{ $category->show_on_cover ? 'Quitar de portada' : 'Mostrar en portada' }}
                                    </button>
                                </form>
                                <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Editar nombre público</a>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h4 class="h6 text-muted">Subcategorías</h4>
                            @if($category->subcategories->count())
                                <ul class="list-group mb-3">
                                    @foreach($category->subcategories as $subcategory)
                                        <li class="list-group-item d-flex align-items-center gap-2 flex-wrap">
                                            <form method="POST" action="{{ route('category-subcategories.update', $subcategory) }}" class="d-flex align-items-center gap-2 flex-fill">
                                                @csrf
                                                @method('PATCH')
                                                <input type="text" name="name" class="form-control form-control-sm" value="{{ $subcategory->name }}" required>
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Guardar</button>
                                            </form>
                                            <form method="POST" action="{{ route('category-subcategories.destroy', $subcategory) }}">
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
                            <form method="POST" action="{{ route('category-subcategories.store', $category) }}" class="row g-2 align-items-end">
                                @csrf
                                <div class="col-md-8">
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="Nombre de la subcategoría" required>
                                </div>
                                <div class="col-md-4 text-end">
                                    <button type="submit" class="btn btn-sm btn-success w-100">Agregar</button>
                                </div>
                            </form>
                        </div>
                        @if($category->dishes->count() > 0)
                            <ul class="list-group dish-sortable" data-category="{{ $category->id }}">
                                @foreach($category->dishes as $dish)
                                    <li class="list-group-item d-flex align-items-center gap-2 sortable-item" data-id="{{ $dish->id }}">
                                        <span class="text-muted">&#x2630;</span>
                                        <span class="flex-fill">
                                            {{ $dish->name }} - ${{ $dish->price }}
                                            @if($dish->subcategory)
                                                <span class="badge bg-info text-dark ms-2">{{ $dish->subcategory->name }}</span>
                                            @endif
                                            @if($dish->featured_on_cover)
                                                <span class="badge bg-warning text-dark ms-2">Destacado</span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                            <small class="text-muted">Arrastra para reordenar rápidamente.</small>

                            <form method="POST" action="{{ route('categories.featuredItems', $category) }}" class="mt-3">
                                @csrf
                                <p class="text-muted small mb-2">Marca los platos que deben aparecer como Lo más vendido en esta pestaña:</p>
                                <div class="row g-2">
                                    @foreach($category->dishes as $dish)
                                        <div class="col-sm-6 col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="featured_items[]" value="{{ $dish->id }}" id="dish-featured-{{ $dish->id }}" {{ $dish->featured_on_cover ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="dish-featured-{{ $dish->id }}">
                                                    {{ $dish->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary mt-3">Guardar destacados</button>
                            </form>
                        @else
                            <p>No dishes found for this category.</p>
                        @endif
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-outline-primary">Editar</a>
                            <button class="btn btn-outline-danger" form="delete-category-{{ $category->id }}">Eliminar</button>
                        </div>
                        <form method="POST" action="{{ route('categories.update', $category) }}" class="mt-3 row g-2 align-items-end border rounded-3 p-3 bg-light">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="name" value="{{ $category->name }}">
                            <div class="col-md-4">
                                <label class="form-label text-muted small mb-1">Nombre público</label>
                                <input type="text" name="cover_title" class="form-control form-control-sm" value="{{ $category->cover_title }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small mb-1">Descripción breve</label>
                                <input type="text" name="cover_subtitle" class="form-control form-control-sm" value="{{ $category->cover_subtitle }}">
                            </div>
                            <div class="col-md-2 form-check mt-4">
                                <input type="hidden" name="show_on_cover" value="0">
                                <input type="checkbox" class="form-check-input" id="cover-show-{{ $category->id }}" name="show_on_cover" value="1" {{ $category->show_on_cover ? 'checked' : '' }}>
                                <label class="form-check-label small" for="cover-show-{{ $category->id }}">Mostrar en portada</label>
                            </div>
                            <div class="col-md-2 text-end">
                                <button type="submit" class="btn btn-sm btn-primary w-100">Guardar</button>
                            </div>
                        </form>
                        <form id="delete-category-{{ $category->id }}" method="POST" action="{{ route('categories.destroy', $category) }}" style="display:none;">
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
    function filterDishes() {
        let input = document.getElementById('searchDishesInput');
        let filter = input.value.toUpperCase();
        let dishesList = document.getElementById('dishesList');
        let dishItems = dishesList.getElementsByClassName('dish-item');

        for (let i = 0; i < dishItems.length; i++) {
            let name = dishItems[i].getAttribute('data-name');
            if (name.toUpperCase().indexOf(filter) > -1) {
                dishItems[i].style.display = "";
            } else {
                dishItems[i].style.display = "none";
            }
        }
    }
</script>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const saveCategoryOrder = (orderedIds) => {
            fetch('{{ route('categories.reorder') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ order: orderedIds }),
            }).then(response => {
                if (!response.ok) throw new Error('HTTP '+response.status);
                return response.json();
            }).then(data => {
                if (!data.success) {
                    alert('No se pudo guardar el orden de categorías.');
                }
            }).catch(() => alert('Error al guardar el orden de categorías.'));
        };

        document.querySelectorAll('.category-sortable').forEach(list => {
            new Sortable(list, {
                animation: 150,
                handle: '.sortable-handle',
                ghostClass: 'bg-light',
                onEnd: evt => {
                    const order = Array.from(evt.to.querySelectorAll('.sortable-item')).map(item => item.dataset.id);
                    saveCategoryOrder(order);
                }
            });
        });

        document.querySelectorAll('.category-card-sortable').forEach(list => {
            new Sortable(list, {
                animation: 150,
                handle: '.sortable-handle',
                ghostClass: 'bg-light',
                onEnd: evt => {
                    const order = Array.from(evt.to.querySelectorAll('.category-card')).map(item => item.dataset.id);
                    saveCategoryOrder(order);
                }
            });
        });

        document.querySelectorAll('.dish-sortable').forEach(list => {
            new Sortable(list, {
                animation: 150,
                ghostClass: 'bg-light',
                onEnd: evt => {
                    const categoryId = evt.to.dataset.category;
                    const order = Array.from(evt.to.querySelectorAll('li')).map(item => item.dataset.id);

                    fetch('{{ route('dishes.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            category_id: categoryId,
                            order: order,
                        }),
                    }).then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP '+response.status);
                        }
                        return response.json();
                    }).then(data => {
                        if (!data.success) {
                            alert('No se pudo guardar el orden.');
                        }
                    }).catch(() => alert('Error al guardar el orden.'));
                }
            });
        });
    });
</script>
@endpush
