<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Categoría - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 960px;
            margin: auto;
            padding-top: 50px;
        }
        .card {
            margin-top: 20px;
        }
        .btn {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Crear Categoría</h1>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Nueva Categoría</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('categories.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre de la categoría:</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="show_on_cover" name="show_on_cover" value="1" {{ old('show_on_cover') ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_on_cover">Mostrar en la portada como pestaña</label>
                            </div>
                            <div class="mb-3">
                                <label for="cover_title" class="form-label">Nombre público (opcional)</label>
                                <input type="text" class="form-control" id="cover_title" name="cover_title" value="{{ old('cover_title') }}" placeholder="Ej. Brunch Salado">
                            </div>
                            <div class="mb-3">
                                <label for="cover_subtitle" class="form-label">Descripción breve</label>
                                <input type="text" class="form-control" id="cover_subtitle" name="cover_subtitle" value="{{ old('cover_subtitle') }}" placeholder="Se mostrará bajo el título en la portada">
                            </div>
                            <div class="mb-3">
                                <label for="tax_ids" class="form-label">Impuestos aplicados</label>
                                <select id="tax_ids" name="tax_ids[]" class="form-select" multiple>
                                    @foreach($taxes as $tax)
                                        <option value="{{ $tax->id }}" {{ collect(old('tax_ids', []))->contains($tax->id) ? 'selected' : '' }}>
                                            {{ $tax->name }} · {{ number_format($tax->rate, 2) }}%
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Puedes asignar varios impuestos a la categoría.</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Crear</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
