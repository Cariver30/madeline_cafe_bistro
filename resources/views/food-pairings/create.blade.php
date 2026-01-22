<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Maridaje</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Crear Maridaje (Food Pairing)</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('food-pairings.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nombre del Maridaje</label>
            <input type="text" class="form-control" id="name" name="name" required value="{{ old('name') }}">
        </div>

        <div class="mb-3">
            <label for="dish_id" class="form-label">Plato Relacionado</label>
            <select class="form-control" id="dish_id" name="dish_id" required>
                <option value="">Seleccionar plato...</option>
                @foreach ($dishes as $dish)
                    <option value="{{ $dish->id }}" {{ old('dish_id') == $dish->id ? 'selected' : '' }}>
                        {{ $dish->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success">Guardar Maridaje</button>
        <a href="{{ route('admin.new-panel', ['section' => 'wines-section']) }}" class="btn btn-secondary">Volver</a>
    </form>
</div>
</body>
</html>
