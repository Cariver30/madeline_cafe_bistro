<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel de Administración</title>
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
        }
        h1, h2 {
            text-align: center;
        }
        .img-fluid {
            max-width: 200px;
            height: auto;
        }
        .card {
            margin-bottom: 20px;
        }
        .btn {
            margin: 5px;
        }
        .hidden {
            display: none;
        }
        @media (max-width: 768px) {
            .container {
                max-width: 100%;
                padding: 0 15px;
            }
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
<div class="container py-5">
    <h1>Panel de Administración</h1>

    <section class="mt-5">
    <h2>Configuraciones Generales</h2>

    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="logo" class="form-label">Logo</label>
            <input type="file" class="form-control" id="logo" name="logo">
            @if($settings && $settings->logo)
                <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" class="img-fluid mt-2">
            @endif
        </div>

        <div class="mb-3">
            <label for="facebook_url" class="form-label">Facebook URL</label>
            <input type="url" class="form-control" id="facebook_url" name="facebook_url" value="{{ $settings->facebook_url ?? '' }}">
        </div>
        <div class="mb-3">
            <label for="twitter_url" class="form-label">Twitter URL</label>
            <input type="url" class="form-control" id="twitter_url" name="twitter_url" value="{{ $settings->twitter_url ?? '' }}">
        </div>
        <div class="mb-3">
            <label for="instagram_url" class="form-label">Instagram URL</label>
            <input type="url" class="form-control" id="instagram_url" name="instagram_url" value="{{ $settings->instagram_url ?? '' }}">
        </div>
        <div class="mb-3">
            <label for="phone_number" class="form-label">Número de Teléfono</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ $settings->phone_number ?? '' }}">
        </div>
        <div class="mb-3">
            <label for="business_hours" class="form-label">Horarios de Atención</label>
            <textarea class="form-control" id="business_hours" name="business_hours">{{ $settings->business_hours ?? '' }}</textarea>
        </div>

        <div class="mb-3">
            <label for="background_image_cover" class="form-label">Imagen de Fondo Cover</label>
            <input type="file" class="form-control" id="background_image_cover" name="background_image_cover">
        </div>
        <div class="mb-3">
            <label for="background_image_menu" class="form-label">Imagen de Fondo Menu</label>
            <input type="file" class="form-control" id="background_image_menu" name="background_image_menu">
        </div>
        <div class="mb-3">
            <label for="background_image_cocktails" class="form-label">Imagen de Fondo de Cocktails</label>
            <input type="file" class="form-control" id="background_image_cocktails" name="background_image_cocktails">
        </div>
        <div class="mb-3">
            <label for="background_image_wines" class="form-label">Imagen de Fondo de Café</label>
            <input type="file" class="form-control" id="background_image_wines" name="background_image_wines">
        </div>
        <div class="mb-3">
            <label for="text_color_cover" class="form-label">Color del Texto de Cover</label>
            <input type="color" class="form-control" id="text_color_cover" name="text_color_cover" value="{{ $settings->text_color_cover ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="text_color_menu" class="form-label">Color del Texto de Menú</label>
            <input type="color" class="form-control" id="text_color_menu" name="text_color_menu" value="{{ $settings->text_color_menu ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="text_color_cocktails" class="form-label">Color del Texto de Cocktails</label>
            <input type="color" class="form-control" id="text_color_cocktails" name="text_color_cocktails" value="{{ $settings->text_color_cocktails ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="text_color_wines" class="form-label">Color del Texto de Café</label>
            <input type="color" class="form-control" id="text_color_wines" name="text_color_wines" value="{{ $settings->text_color_wines ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="font_family_cover" class="form-label">Familia de Fuente de Cover</label>
            <input type="text" class="form-control" id="font_family_cover" name="font_family_cover" value="{{ $settings->font_family_cover ?? 'Arial' }}">
        </div>
        <div class="mb-3">
            <label for="font_family_menu" class="form-label">Familia de Fuente de Menú</label>
            <input type="text" class="form-control" id="font_family_menu" name="font_family_menu" value="{{ $settings->font_family_menu ?? 'Arial' }}">
        </div>
        <div class="mb-3">
            <label for="font_family_cocktails" class="form-label">Familia de Fuente de Cocktails</label>
            <input type="text" class="form-control" id="font_family_cocktails" name="font_family_cocktails" value="{{ $settings->font_family_cocktails ?? 'Arial' }}">
        </div>
        <div class="mb-3">
            <label for="font_family_wines" class="form-label">Familia de Fuente de Café</label>
            <input type="text" class="form-control" id="font_family_wines" name="font_family_wines" value="{{ $settings->font_family_wines ?? 'Arial' }}">
        </div>
        <div class="mb-3">
            <label for="card_opacity_cover" class="form-label">Opacidad de las Tarjetas de Cover</label>
            <input type="number" step="0.1" class="form-control" id="card_opacity_cover" name="card_opacity_cover" value="{{ $settings->card_opacity_cover ?? 1 }}">
        </div>
        <div class="mb-3">
            <label for="card_opacity_menu" class="form-label">Opacidad de las Tarjetas de Menú</label>
            <input type="number" step="0.1" class="form-control" id="card_opacity_menu" name="card_opacity_menu" value="{{ $settings->card_opacity_menu ?? 1 }}">
        </div>
        <div class="mb-3">
            <label for="card_opacity_cocktails" class="form-label">Opacidad de las Tarjetas de Cocktails</label>
            <input type="number" step="0.1" class="form-control" id="card_opacity_cocktails" name="card_opacity_cocktails" value="{{ $settings->card_opacity_cocktails ?? 1 }}">
        </div>
        <div class="mb-3">
            <label for="card_opacity_wines" class="form-label">Opacidad de las Tarjetas de Café</label>
            <input type="number" step="0.1" class="form-control" id="card_opacity_wines" name="card_opacity_wines" value="{{ $settings->card_opacity_wines ?? 1 }}">
        </div>
        <div class="mb-3">
            <label for="button_color_cover" class="form-label">Color del Botón de Cover</label>
            <input type="color" class="form-control" id="button_color_cover" name="button_color_cover" value="{{ $settings->button_color_cover ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="button_color_menu" class="form-label">Color del Botón de Menú</label>
            <input type="color" class="form-control" id="button_color_menu" name="button_color_menu" value="{{ $settings->button_color_menu ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="button_color_cocktails" class="form-label">Color del Botón de Cocktails</label>
            <input type="color" class="form-control" id="button_color_cocktails" name="button_color_cocktails" value="{{ $settings->button_color_cocktails ?? '#000000' }}">
        </div>
        <div class="mb-3">
            <label for="button_color_wines" class="form-label">Color del Botón de Café</label>
            <input type="color" class="form-control" id="button_color_wines" name="button_color_wines" value="{{ $settings->button_color_wines ?? '#000000' }}">
        </div>

        <!-- Configuraciones de Categorías -->
        <div class="form-group">
            <label for="category_name_bg_color_menu">Color de fondo de la categoría (Menú):</label>
            <input type="color" class="form-control" id="category_name_bg_color_menu" name="category_name_bg_color_menu" value="{{ $settings->category_name_bg_color_menu ?? '#ffffff' }}">
        </div>
        <div class="form-group">
            <label for="category_name_text_color_menu">Color de texto de la categoría (Menú):</label>
            <input type="color" class="form-control" id="category_name_text_color_menu" name="category_name_text_color_menu" value="{{ $settings->category_name_text_color_menu ?? '#000000' }}">
        </div>
        <div class="form-group">
            <label for="category_name_font_size_menu">Tamaño de fuente de la categoría (Menú):</label>
            <input type="number" class="form-control" id="category_name_font_size_menu" name="category_name_font_size_menu" value="{{ $settings->category_name_font_size_menu ?? 16 }}">
        </div>

        <div class="form-group">
            <label for="category_name_bg_color_cocktails">Color de fondo de la categoría (Cocktails):</label>
            <input type="color" class="form-control" id="category_name_bg_color_cocktails" name="category_name_bg_color_cocktails" value="{{ $settings->category_name_bg_color_cocktails ?? '#ffffff' }}">
        </div>
        <div class="form-group">
            <label for="category_name_text_color_cocktails">Color de texto de la categoría (Cocktails):</label>
            <input type="color" class="form-control" id="category_name_text_color_cocktails" name="category_name_text_color_cocktails" value="{{ $settings->category_name_text_color_cocktails ?? '#000000' }}">
        </div>
        <div class="form-group">
            <label for="category_name_font_size_cocktails">Tamaño de fuente de la categoría (Cocktails):</label>
            <input type="number" class="form-control" id="category_name_font_size_cocktails" name="category_name_font_size_cocktails" value="{{ $settings->category_name_font_size_cocktails ?? 16 }}">
        </div>

        <div class="form-group">
            <label for="category_name_bg_color_wines">Color de fondo de la categoría (Wines):</label>
            <input type="color" class="form-control" id="category_name_bg_color_wines" name="category_name_bg_color_wines" value="{{ $settings->category_name_bg_color_wines ?? '#ffffff' }}">
        </div>
        <div class="form-group">
            <label for="category_name_text_color_wines">Color de texto de la categoría (Wines):</label>
            <input type="color" class="form-control" id="category_name_text_color_wines" name="category_name_text_color_wines" value="{{ $settings->category_name_text_color_wines ?? '#000000' }}">
        </div>
        <div class="form-group">
            <label for="category_name_font_size_wines">Tamaño de fuente de la categoría (Wines):</label>
            <input type="number" class="form-control" id="category_name_font_size_wines" name="category_name_font_size_wines" value="{{ $settings->category_name_font_size_wines ?? 16 }}">
        </div>

        <div class="form-group">
    <label for="card_bg_color_menu">Color de fondo de la tarjeta (Menú):</label>
    <input type="color" id="card_bg_color_menu" name="card_bg_color_menu" value="{{ $settings->card_bg_color_menu ?? '#ffffff' }}">
</div>
<div class="form-group">
    <label for="card_bg_color_cocktails">Color de fondo de la tarjeta (Cocktails):</label>
    <input type="color" id="card_bg_color_cocktails" name="card_bg_color_cocktails" value="{{ $settings->card_bg_color_cocktails ?? '#ffffff' }}">
</div>
<div class="form-group">
    <label for="card_bg_color_wines">Color de fondo de la tarjeta (Café):</label>
    <input type="color" id="card_bg_color_wines" name="card_bg_color_wines" value="{{ $settings->card_bg_color_wines ?? '#ffffff' }}">
</div>


        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</section>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




<section class="mt-5">
    <h2>Platos</h2>
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
</section>



        <!-- Categorías -->
        <section class="mt-5">
            <h2>Categorías</h2>
            <button class="btn btn-primary mb-3" onclick="toggleVisibility('categories')">Categorías</button>
            <div id="categories" class="hidden">
                <a href="{{ route('categories.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear nueva categoría</a>
                <div id="categoriesList">
                    @foreach($categories as $category)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h3 class="card-title">{{ $category->name }}</h3>
                                @if($category->dishes->count() > 0)
                                    <ul>
                                        @foreach($category->dishes as $dish)
                                            <li>{{ $dish->name }} - ${{ $dish->price }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>No dishes found for this category.</p>
                                @endif
                                <a href="{{ route('categories.edit', $category) }}" class="btn btn-outline-primary">Editar</a>
                                <button class="btn btn-outline-danger" form="delete-category-{{ $category->id }}">Eliminar</button>
                                <form id="delete-category-{{ $category->id }}" method="POST" action="{{ route('categories.destroy', $category) }}" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Cocktails -->
<section class="mt-5">
    <h2>Cocktails</h2>
    <button class="btn btn-primary mb-3" onclick="toggleVisibility('cocktails')">Gestionar Cocktails</button>
    <input type="text" id="searchCocktails" onkeyup="filterItems('cocktailsList', 'cocktail-item', 'searchCocktails')" placeholder="Buscar cocktails..." class="form-control mb-3">
    <div id="cocktails" class="hidden">
        <a href="{{ route('cocktails.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear nuevo cocktail</a>
        <div id="cocktailsList">
            @foreach($cocktails as $cocktail)
                <div class="card mb-3 cocktail-item" data-name="{{ $cocktail->name }}" data-category="{{ $cocktail->category->name }}">
                    <div class="card-body">
                        <h3 class="card-title">{{ $cocktail->name }}</h3>
                        <img src="{{ asset('storage/' . $cocktail->image) }}" alt="{{ $cocktail->name }}" class="img-fluid">
                        <p class="card-text">{{ $cocktail->description }}</p>
                        <p class="card-text">${{ $cocktail->price }}</p>
                        <a href="{{ route('cocktails.edit', $cocktail) }}" class="btn btn-outline-primary">Editar</a>
                        <button class="btn btn-outline-danger" form="delete-cocktail-{{ $cocktail->id }}">Eliminar</button>
                        <form id="delete-cocktail-{{ $cocktail->id }}" method="POST" action="{{ route('cocktails.destroy', $cocktail) }}" style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
                        <form method="POST" action="{{ route('cocktails.toggleVisibility', $cocktail) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn {{ $cocktail->visible ? 'btn-success' : 'btn-warning' }}">
                                {{ $cocktail->visible ? 'Visible' : 'Oculto' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>


        <!-- Categorías de Cocktails -->
        <section class="mt-5">
            <h2>Categorías de Cocktails</h2>
            <button class="btn btn-primary mb-3" onclick="toggleVisibility('cocktail-categories')">Categorías</button>
            <div id="cocktail-categories" class="hidden">
                <a href="{{ route('cocktail-categories.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear nueva categoría de cocktail</a>
                <div id="cocktailCategoriesList">
                    @foreach($cocktailCategories as $category)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h3 class="card-title">{{ $category->name }}</h3>
                                @if($category->items->count() > 0)
                                    <ul>
                                        @foreach($category->items as $item)
                                            <li>{{ $item->name }} - ${{ $item->price }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>No items found for this category.</p>
                                @endif
                                <a href="{{ route('cocktail-categories.edit', $category) }}" class="btn btn-outline-primary">Editar</a>
                                <button class="btn btn-outline-danger" form="delete-cocktail-category-{{ $category->id }}">Eliminar</button>
                                <form id="delete-cocktail-category-{{ $category->id }}" method="POST" action="{{ route('cocktail-categories.destroy', $category) }}" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

<!-- Café -->
<section class="mt-5">
    <h2>Bebidas de Café</h2>
    <button class="btn btn-primary mb-3" onclick="toggleVisibility('wines')">Gestionar bebidas</button>
    <input type="text" id="searchWinesInput" onkeyup="filterWines()" placeholder="Buscar bebidas..." class="form-control mb-3">

    <div id="wines" class="hidden">
        <a href="{{ route('wines.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Registrar nueva bebida</a>
        <div id="winesList">
            @foreach($wines as $wine)
                <div class="card mb-3 wine-item" data-name="{{ $wine->name }}" data-category="{{ $wine->category->name }}">
                    <div class="card-body">
                        <h3 class="card-title">{{ $wine->name }}</h3>
                        <img src="{{ asset('storage/' . $wine->image) }}" alt="{{ $wine->name }}" class="img-fluid">
                        <p class="card-text">{{ $wine->description }}</p>
                        <p class="card-text">${{ $wine->price }}</p>
                        <a href="{{ route('wines.edit', $wine) }}" class="btn btn-outline-primary">Editar</a>
                        <button class="btn btn-outline-danger" form="delete-wine-{{ $wine->id }}">Eliminar</button>
                        <form id="delete-wine-{{ $wine->id }}" method="POST" action="{{ route('wines.destroy', $wine) }}" style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
                        <form method="POST" action="{{ route('wines.toggleVisibility', $wine) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-secondary">
                                {{ $wine->visible ? 'Ocultar' : 'Mostrar' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

        <!-- Categorías de Café -->
        <section class="mt-5">
            <h2>Categorías de Café</h2>
            <button class="btn btn-primary mb-3" onclick="toggleVisibility('wine-categories')">Categorías</button>
            <div id="wine-categories" class="hidden">
                <a href="{{ route('wine-categories.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear nueva categoría</a>
                <div id="wineCategoriesList">
                    @foreach($wineCategories as $category)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h3 class="card-title">{{ $category->name }}</h3>
                                @if($category->items->count() > 0)
                                    <ul>
                                        @foreach($category->items as $item)
                                            <li>{{ $item->name }} - ${{ $item->price }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>No items found for this category.</p>
                                @endif
                                <a href="{{ route('wine-categories.edit', $category) }}" class="btn btn-outline-primary">Editar</a>
                                <button class="btn btn-outline-danger" form="delete-wine-category-{{ $category->id }}">Eliminar</button>
                                <form id="delete-wine-category-{{ $category->id }}" method="POST" action="{{ route('wine-categories.destroy', $category) }}" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.10.2/Sortable.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('categoriesList');
            var sortable = Sortable.create(el, {
                onEnd: function (/**Event*/evt) {
                    var itemEl = evt.item;  // dragged HTMLElement
                    updateCategoryOrder(); // calls a function that handles the order update
                },
            });

            var updateOrderButton = document.getElementById('updateOrderButton');
            if(updateOrderButton) {
                updateOrderButton.addEventListener('click', updateCategoryOrder);
            }
        });

        function updateCategoryOrder() {
            var categories = document.getElementById('categoriesList').children;
            var categoryOrder = [];
            for (var i = 0; i < categories.length; i++) {
                var categoryId = categories[i].id.split('-')[1];
                categoryOrder.push(categoryId);
            }

            fetch('/update-category-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ order: categoryOrder })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                if (response.headers.get('content-type').includes('application/json')) {
                    return response.json();
                } else {
                    console.error('Server response is not a valid JSON');
                    return;
                }
            })
            .then(data => {
                if (data && data.success) {
                    alert('Order updated successfully');
                } else {
                    alert('Failed to update order');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function filterDishes() {
    let input = document.getElementById('searchInput');
    let filter = input.value.toUpperCase();
    let dishesList = document.getElementById('dishesList');
    let dishItems = dishesList.getElementsByClassName('dish-item');

    for (let i = 0; i < dishItems.length; i++) {
        let name = dishItems[i].getAttribute('data-name');
        if (name.toUpperCase().indexOf(filter) > -1) {
            dishItems[i].style.display = "";
        } else {
            dishItems[i].style.display = "none";
        }
    }
}

function toggleVisibility(sectionId) {
    const section = document.getElementById(sectionId);
    section.classList.toggle('hidden');
}

        function filterCocktails() {
            let input = document.getElementById("searchCocktailInput");
            let filter = input.value.toUpperCase();
            let cocktailsList = document.getElementById("cocktailsList");
            let cocktails = cocktailsList.getElementsByClassName("cocktail-item");

            for (let i = 0; i < cocktails.length; i++) {
                let name = cocktails[i].getAttribute("data-name");
                let category = cocktails[i].getAttribute("data-category");
                if (name.toUpperCase().indexOf(filter) > -1 || category.toUpperCase().indexOf(filter) > -1) {
                    cocktails[i].style.display = "";
                } else {
                    cocktails[i].style.display = "none";
                }
            }
        }

        function filterWines() {
    let input = document.getElementById('searchWinesInput');
    let filter = input.value.toUpperCase();
    let winesList = document.getElementById('winesList');
    let wineItems = winesList.getElementsByClassName('wine-item');

    for (let i = 0; i < wineItems.length; i++) {
        let name = wineItems[i].getAttribute('data-name');
        if (name.toUpperCase().indexOf(filter) > -1) {
            wineItems[i].style.display = "";
        } else {
            wineItems[i].style.display = "none";
        }
    }
}

function toggleVisibility(sectionId) {
    const section = document.getElementById(sectionId);
    section.classList.toggle('hidden');
}

    </script>
</body>
</html>
