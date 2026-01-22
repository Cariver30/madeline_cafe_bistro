<div>
    <h2>Gestionar Platos</h2>
    <button class="btn btn-primary mb-3" onclick="toggleVisibility('dishes')">Gestionar Platos</button>
    <input type="text" id="searchInput" onkeyup="filterDishes()" placeholder="Buscar platos..." class="form-control mb-3">
    <div id="dishes" class="hidden">
        <a href="{{ route('dishes.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear nuevo plato</a>
        <div id="dishesList">
            @foreach($dishes as $dish)
                <div class="card mb-3 dish-item" data-name="{{ $dish->name }}" data-category="{{ $dish->category->name }}">
                    <div class="card-body">
                        <h3 class="card-title">{{ $dish->name }}</h3>
                        <img src="{{ asset('storage/' . $dish->image) }}" alt="{{ $dish->name }}" class="img-fluid">
                        <p class="card-text">{{ $dish->description }}</p>
                        <p class="card-text">${{ $dish->price }}</p>
                        <a href="{{ route('dishes.edit', $dish) }}" class="btn btn-outline-primary">Editar</a>
                        <button class="btn btn-outline-danger" form="delete-dish-{{ $dish->id }}">Eliminar</button>
                        <form id="delete-dish-{{ $dish->id }}" method="POST" action="{{ route('dishes.destroy', $dish) }}" style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
                        <form method="POST" action="{{ route('dishes.toggleVisibility', $dish) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-secondary">
                                {{ $dish->visible ? 'Ocultar' : 'Mostrar' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
