<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Categoría de Cantina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Crear Categoría de Cantina</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('cantina-categories.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="cover_title" class="form-label">Nombre público</label>
            <input type="text" class="form-control" id="cover_title" name="cover_title">
        </div>
        <div class="mb-3">
            <label for="cover_subtitle" class="form-label">Descripción breve</label>
            <input type="text" class="form-control" id="cover_subtitle" name="cover_subtitle">
        </div>
        <div class="form-check mb-3">
            <input type="hidden" name="show_on_cover" value="0">
            <input class="form-check-input" type="checkbox" id="show_on_cover" name="show_on_cover" value="1">
            <label class="form-check-label" for="show_on_cover">Mostrar en portada</label>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
</body>
</html>
