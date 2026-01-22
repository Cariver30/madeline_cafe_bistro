<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Maridajes</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Lista de Maridajes</h2>

    <a href="{{ route('food-pairings.create') }}" class="btn btn-success mb-3">
        <i class="fas fa-plus"></i> Crear nuevo maridaje
    </a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre del Maridaje</th>
                <th>Plato Asociado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($foodPairings as $pairing)
                <tr>
                    <td>{{ $pairing->name }}</td>
                    <td>{{ $pairing->dish->name ?? 'Sin plato' }}</td>
                    <td>
                        <a href="{{ route('food-pairings.edit', $pairing) }}" class="btn btn-primary btn-sm">Editar</a>
                        <form action="{{ route('food-pairings.destroy', $pairing) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este maridaje?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No hay maridajes registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
