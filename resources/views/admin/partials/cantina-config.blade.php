<!-- resources/views/admin/partials/cantina-config.blade.php -->
@php $cantinaLabel = $cantinaLabel ?? 'Cantina'; @endphp
<div class="cantina-config space-y-4">
    <form action="{{ route('admin.updateBackground') }}" method="POST" enctype="multipart/form-data" class="space-y-6 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        @csrf

        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">🍸 Configuración de {{ $cantinaLabel }}</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">Ajusta colores, fondos y tarjetas de la vista de {{ $cantinaLabel }}. Sigue el mismo orden del menú.</p>

        {{-- Imagen de fondo --}}
        <div>
            <label for="background_image_cantina" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Imagen de Fondo de {{ $cantinaLabel }}</label>
            <input type="file" name="background_image_cantina" id="background_image_cantina" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 dark:border-gray-600 dark:placeholder-gray-400 focus:outline-none">
        </div>

        {{-- Color de texto --}}
        <div>
            <label for="text_color_cantina" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color del Texto</label>
            <input type="color" name="text_color_cantina" id="text_color_cantina" value="{{ $settings->text_color_cantina ?? '#000000' }}"
                   class="w-16 h-10 p-1 border rounded-md">
        </div>

        {{-- Opacidad de tarjeta --}}
        <div>
            <label for="card_opacity_cantina" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Opacidad de las Tarjetas</label>
            <input type="number" step="0.1" name="card_opacity_cantina" id="card_opacity_cantina"
                   value="{{ $settings->card_opacity_cantina ?? 1 }}"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-24 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>

        {{-- Color del botón --}}
        <div>
            <label for="button_color_cantina" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color del Botón</label>
            <input type="color" name="button_color_cantina" id="button_color_cantina" value="{{ $settings->button_color_cantina ?? '#000000' }}"
                   class="w-16 h-10 p-1 border rounded-md">
        </div>

        {{-- Color de fondo de categoría --}}
        <div>
            <label for="category_name_bg_color_cantina" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color de Fondo de Categoría</label>
            <input type="color" name="category_name_bg_color_cantina" id="category_name_bg_color_cantina" value="{{ $settings->category_name_bg_color_cantina ?? '#ffffff' }}"
                   class="w-16 h-10 p-1 border rounded-md">
        </div>

        {{-- Color de texto de categoría --}}
        <div>
            <label for="category_name_text_color_cantina" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color de Texto de Categoría</label>
            <input type="color" name="category_name_text_color_cantina" id="category_name_text_color_cantina" value="{{ $settings->category_name_text_color_cantina ?? '#000000' }}"
                   class="w-16 h-10 p-1 border rounded-md">
        </div>

        {{-- Tamaño de fuente de categoría --}}
        <div>
            <label for="category_name_font_size_cantina" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tamaño de Fuente de Categoría (px)</label>
            <input type="number" name="category_name_font_size_cantina" id="category_name_font_size_cantina" value="{{ $settings->category_name_font_size_cantina ?? 16 }}"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-24 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>

        {{-- Color de fondo de tarjeta --}}
        <div>
            <label for="card_bg_color_cantina" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color de Fondo de Tarjetas</label>
            <input type="color" name="card_bg_color_cantina" id="card_bg_color_cantina" value="{{ $settings->card_bg_color_cantina ?? '#ffffff' }}"
                   class="w-16 h-10 p-1 border rounded-md">
        </div>

        <button type="submit"
                class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">
            Guardar Cambios
        </button>
    </form>

    <style>
        .cantina-config input::placeholder,
        .cantina-config textarea::placeholder {
            color: #000000;
            opacity: 1;
        }
    </style>
</div>
