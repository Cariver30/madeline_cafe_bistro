<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Uvas</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome (opcional para íconos) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Lista de Uvas</h2>

    <a href="{{ route('grapes.create') }}" class="btn btn-success mb-3">
        <i class="fas fa-plus"></i> Crear nueva uva
    </a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($grapes as $grape)
                <tr>
                    <td>{{ $grape->name }}</td>
                    <td>
                        <a href="{{ route('grapes.edit', $grape) }}" class="btn btn-primary btn-sm">Editar</a>
                        <form action="{{ route('grapes.destroy', $grape) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta uva?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">No hay uvas registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Bootstrap JS + FontAwesome -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
