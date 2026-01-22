<!-- resources/views/admin/partials/wines-config.blade.php -->
<div class="wines-config space-y-4">
    <form action="{{ route('admin.updateBackground') }}" method="POST" enctype="multipart/form-data" class="space-y-6 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        @csrf

        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">☕ Configuración de Café &amp; Bebidas</h3>

    {{-- Imagen de fondo --}}
        <div>
            <label for="background_image_wines" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Imagen de Fondo (barra de café)</label>
            <input type="file" name="background_image_wines" id="background_image_wines" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 dark:border-gray-600 placeholder-black focus:outline-none">
        </div>

    {{-- Imagen destacada --}}
        <div>
            <label for="coffee_hero_image" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Imagen destacada del menú (hero)</label>
            <input type="file" name="coffee_hero_image" id="coffee_hero_image" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 placeholder-black">
            @if($settings->coffee_hero_image)
                <div class="mt-2 space-y-2">
                    <img src="{{ asset('storage/' . $settings->coffee_hero_image) }}" class="rounded-lg shadow w-full max-h-64 object-cover" alt="Hero café">
                    <button type="submit" name="remove_coffee_hero_image" value="1"
                            class="text-sm px-4 py-2 rounded-lg border border-red-500 text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                        Eliminar imagen
                    </button>
                </div>
            @endif
        </div>

    {{-- Color de texto --}}
    <div>
        <label for="text_color_wines" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color del Texto</label>
        <input type="color" name="text_color_wines" id="text_color_wines" value="{{ $settings->text_color_wines ?? '#000000' }}"
               class="w-16 h-10 p-1 border rounded-md">
    </div>

    {{-- Opacidad de tarjeta --}}
    <div>
        <label for="card_opacity_wines" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Opacidad de las Tarjetas</label>
        <input type="number" step="0.1" name="card_opacity_wines" id="card_opacity_wines"
               value="{{ $settings->card_opacity_wines ?? 1 }}"
               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-24 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
    </div>

    {{-- Color del botón --}}
    <div>
        <label for="button_color_wines" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color del Botón</label>
        <input type="color" name="button_color_wines" id="button_color_wines" value="{{ $settings->button_color_wines ?? '#000000' }}"
               class="w-16 h-10 p-1 border rounded-md">
    </div>

    {{-- Color de fondo de categoría --}}
    <div>
        <label for="category_name_bg_color_wines" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color de Fondo de Categoría</label>
        <input type="color" name="category_name_bg_color_wines" id="category_name_bg_color_wines" value="{{ $settings->category_name_bg_color_wines ?? '#000000' }}"
               class="w-16 h-10 p-1 border rounded-md">
    </div>

    {{-- Color de texto de categoría --}}
    <div>
        <label for="category_name_text_color_wines" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color de Texto de Categoría</label>
        <input type="color" name="category_name_text_color_wines" id="category_name_text_color_wines" value="{{ $settings->category_name_text_color_wines ?? '#000000' }}"
               class="w-16 h-10 p-1 border rounded-md">
    </div>

    {{-- Color de fondo de subcategoría --}}
    <div>
        <label for="subcategory_name_bg_color_wines" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color de Fondo de Subcategoría</label>
        <input type="color" name="subcategory_name_bg_color_wines" id="subcategory_name_bg_color_wines" value="{{ $settings->subcategory_name_bg_color_wines ?? '#000000' }}"
               class="w-16 h-10 p-1 border rounded-md">
    </div>

    {{-- Color de texto de subcategoría --}}
    <div>
        <label for="subcategory_name_text_color_wines" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color de Texto de Subcategoría</label>
        <input type="color" name="subcategory_name_text_color_wines" id="subcategory_name_text_color_wines" value="{{ $settings->subcategory_name_text_color_wines ?? '#000000' }}"
               class="w-16 h-10 p-1 border rounded-md">
    </div>

    {{-- Tamaño de fuente de categoría --}}
    <div>
        <label for="category_name_font_size_wines" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tamaño de Fuente de Categoría (px)</label>
        <input type="number" name="category_name_font_size_wines" id="category_name_font_size_wines" value="{{ $settings->category_name_font_size_wines ?? 16 }}"
               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-24 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
    </div>

    {{-- Color de fondo de tarjeta --}}
    <div>
        <label for="card_bg_color_wines" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color de Fondo de Tarjetas</label>
        <input type="color" name="card_bg_color_wines" id="card_bg_color_wines" value="{{ $settings->card_bg_color_wines ?? '#000000' }}"
               class="w-16 h-10 p-1 border rounded-md">
    </div>

        <button type="submit"
                class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">
            Guardar Cambios
        </button>
    </form>

    <style>
        .wines-config input::placeholder,
        .wines-config textarea::placeholder {
            color: #000000;
            opacity: 1;
        }
    </style>
</div>
