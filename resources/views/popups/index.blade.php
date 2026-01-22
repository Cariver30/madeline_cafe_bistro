<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pop-ups</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Pop-ups</h1>
    <a href="{{ route('admin.popups.create') }}" class="btn btn-primary mb-3">Crear nuevo Pop-up</a>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Título</th>
                <th>Vista</th>
                <th>Fecha de inicio</th>
                <th>Fecha de fin</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($popups as $popup)
                <tr>
                    <td>{{ $popup->title }}</td>
                    <td>{{ $popup->view }}</td>
                    <td>{{ $popup->start_date }}</td>
                    <td>{{ $popup->end_date }}</td>
                    <td>{{ $popup->active ? 'Sí' : 'No' }}</td>
                    <td>
                        <a href="{{ route('admin.popups.edit', $popup) }}" class="btn btn-primary btn-sm">Editar</a>
                        <form action="{{ route('admin.popups.destroy', $popup) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
