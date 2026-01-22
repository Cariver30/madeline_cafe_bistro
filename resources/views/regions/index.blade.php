<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Regiones</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome (para el ícono del botón) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Lista de Regiones</h2>

    <a href="{{ route('regions.create') }}" class="btn btn-success mb-3">
        <i class="fas fa-plus"></i> Crear nueva región
    </a>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($regions as $region)
                <tr>
                    <td>{{ $region->name }}</td>
                    <td>
                        <a href="{{ route('regions.edit', $region) }}" class="btn btn-primary btn-sm">Editar</a>
                        <form action="{{ route('regions.destroy', $region) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta región?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">No hay regiones registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Bootstrap JS + FontAwesome -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
