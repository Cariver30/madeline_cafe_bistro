<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar categoría de café</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Editar categoría de café</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('wine-categories.update', $wineCategory->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $wineCategory->name }}" required>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="show_on_cover" name="show_on_cover" value="1" {{ old('show_on_cover', $wineCategory->show_on_cover) ? 'checked' : '' }}>
            <label class="form-check-label" for="show_on_cover">Mostrar en la portada</label>
        </div>
        <div class="mb-3">
            <label for="cover_title" class="form-label">Nombre público</label>
            <input type="text" class="form-control" id="cover_title" name="cover_title" value="{{ old('cover_title', $wineCategory->cover_title) }}">
        </div>
        <div class="mb-3">
            <label for="cover_subtitle" class="form-label">Descripción breve</label>
            <input type="text" class="form-control" id="cover_subtitle" name="cover_subtitle" value="{{ old('cover_subtitle', $wineCategory->cover_subtitle) }}">
        </div>
        <div class="mb-3">
            <label for="tax_ids" class="form-label">Impuestos aplicados</label>
            @php
                $selectedTaxes = collect(old('tax_ids', $wineCategory->taxes->pluck('id')->all()))
                    ->map(fn($value) => (int) $value);
            @endphp
            <select id="tax_ids" name="tax_ids[]" class="form-select" multiple>
                @foreach($taxes as $tax)
                    <option value="{{ $tax->id }}" {{ $selectedTaxes->contains($tax->id) ? 'selected' : '' }}>
                        {{ $tax->name }} · {{ number_format($tax->rate, 2) }}%
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Se aplican a todas las bebidas de esta categoría.</small>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
