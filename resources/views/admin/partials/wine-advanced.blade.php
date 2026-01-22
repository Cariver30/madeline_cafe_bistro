<!-- Métodos de café -->
<section class="mt-5">
    <h3>Métodos de café</h3>
    <button class="btn btn-primary mb-3" onclick="toggleVisibility('wine-types-list')">Gestionar métodos</button>
    <div id="wine-types-list" class="hidden">
        <a href="{{ route('wine-types.create') }}" class="btn btn-success mb-3"><i class="fas fa-plus"></i> Crear nuevo método</a>
        @foreach($wineTypes as $type)
            <div class="card mb-2">
                <div class="card-body">
                    <h5>{{ $type->name }}</h5>
                    <a href="{{ route('wine-types.edit', $type) }}" class="btn btn-outline-primary btn-sm">Editar</a>
                    <form action="{{ route('wine-types.destroy', $type) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm">Eliminar</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Orígenes -->
<section class="mt-5">
    <h3>Orígenes</h3>
    <button class="btn btn-primary mb-3" onclick="toggleVisibility('regions-list')">Gestionar orígenes</button>
    <div id="regions-list" class="hidden">
        <a href="{{ route('regions.create') }}" class="btn btn-success mb-3"><i class="fas fa-plus"></i> Crear nuevo origen</a>
        @foreach($regions as $region)
            <div class="card mb-2">
                <div class="card-body">
                    <h5>{{ $region->name }}</h5>
                    <a href="{{ route('regions.edit', $region) }}" class="btn btn-outline-primary btn-sm">Editar</a>
                    <form action="{{ route('regions.destroy', $region) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm">Eliminar</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Perfiles sensoriales -->
<section class="mt-5">
    <h3>Perfiles sensoriales</h3>
    <button class="btn btn-primary mb-3" onclick="toggleVisibility('grapes-list')">Gestionar perfiles</button>
    <div id="grapes-list" class="hidden">
        <a href="{{ route('grapes.create') }}" class="btn btn-success mb-3"><i class="fas fa-plus"></i> Crear nuevo perfil</a>
        @foreach($grapes as $grape)
            <div class="card mb-2">
                <div class="card-body">
                    <h5>{{ $grape->name }}</h5>
                    <a href="{{ route('grapes.edit', $grape) }}" class="btn btn-outline-primary btn-sm">Editar</a>
                    <form action="{{ route('grapes.destroy', $grape) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm">Eliminar</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Maridajes -->
<section class="mt-5">
    <h3>Maridajes</h3>
    <button class="btn btn-primary mb-3" onclick="toggleVisibility('food-pairings-list')">Gestionar Maridajes</button>
    <div id="food-pairings-list" class="hidden">
        <a href="{{ route('food-pairings.create') }}" class="btn btn-success mb-3"><i class="fas fa-plus"></i> Crear nuevo maridaje</a>
        @foreach($foodPairings as $pairing)
            <div class="card mb-2">
                <div class="card-body">
                    <h5>{{ $pairing->name }}</h5>
                    <a href="{{ route('food-pairings.edit', $pairing) }}" class="btn btn-outline-primary btn-sm">Editar</a>
                    <form action="{{ route('food-pairings.destroy', $pairing) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm">Eliminar</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</section>
