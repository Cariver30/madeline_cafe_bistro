<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Dish;
use Illuminate\Database\Seeder;

class MenuCafeSeeder extends Seeder
{
    /**
     * Seed curated cafe categories and dishes pulled from the legacy menu.
     */
    public function run(): void
    {
        $categories = [
            'desayunos' => [
                'name' => 'Desayunos',
                'order' => 1,
                'show_on_cover' => true,
                'cover_title' => 'Desayunos',
                'cover_subtitle' => 'Jugos naturales, espresso bar y clásicos ligeros.',
            ],
            'brunch' => [
                'name' => 'Brunch',
                'order' => 2,
                'show_on_cover' => true,
                'cover_title' => 'Brunch',
                'cover_subtitle' => 'French toast, waffles y platillos indulgentes.',
            ],
            'sandwiches' => [
                'name' => 'Sandwiches',
                'order' => 3,
                'show_on_cover' => true,
                'cover_title' => 'Sandwiches',
                'cover_subtitle' => 'Panes artesanales, wraps y mezclas saladas.',
            ],
            'postres' => [
                'name' => 'Postres y otros',
                'order' => 4,
                'show_on_cover' => false,
                'cover_title' => 'Postres y otros',
                'cover_subtitle' => 'Para cerrar o acompañar tu ritual.',
            ],
            'chitines' => [
                'name' => 'Menú de chitines',
                'order' => 5,
                'show_on_cover' => false,
                'cover_title' => 'Menú de chitines',
                'cover_subtitle' => 'Porciones pensadas para peques y antojos.',
            ],
        ];

        $categoryIds = [];

        foreach ($categories as $key => $data) {
            $category = Category::updateOrCreate(
                ['name' => $data['name']],
                [
                    'order' => $data['order'],
                    'relevance' => $data['order'],
                    'show_on_cover' => $data['show_on_cover'],
                    'cover_title' => $data['cover_title'],
                    'cover_subtitle' => $data['cover_subtitle'],
                ]
            );

            $categoryIds[$key] = $category->id;
        }

        $dishes = [
            // Desayunos
            [
                'category_key' => 'desayunos',
                'name' => 'Jugos Naturales',
                'description' => 'Selección rotativa de frutas prensadas al momento.',
                'price' => 6.00,
                'position' => 1,
            ],
            [
                'category_key' => 'desayunos',
                'name' => 'Cortadito',
                'description' => 'Blend espresso + leche texturizada.',
                'price' => 2.50,
                'position' => 2,
            ],
            [
                'category_key' => 'desayunos',
                'name' => 'Latte',
                'description' => 'Doble espresso y micro espuma sedosa.',
                'price' => 4.50,
                'position' => 3,
            ],
            [
                'category_key' => 'desayunos',
                'name' => 'Café Mocha',
                'description' => 'Espresso, cacao y espuma cremosa.',
                'price' => 5.00,
                'position' => 4,
            ],
            [
                'category_key' => 'desayunos',
                'name' => 'Capuccino',
                'description' => 'Clásico con doble shot y espuma aireada.',
                'price' => 4.75,
                'position' => 5,
            ],
            [
                'category_key' => 'desayunos',
                'name' => 'Espresso',
                'description' => 'Shot etíope natural con notas de cacao.',
                'price' => 2.25,
                'position' => 6,
            ],
            [
                'category_key' => 'desayunos',
                'name' => 'Coquito Latte',
                'description' => 'Latte con crema de coco especiada y nuez moscada.',
                'price' => 6.00,
                'position' => 7,
                'featured_on_cover' => true,
            ],
            [
                'category_key' => 'desayunos',
                'name' => 'Chocolate Caliente',
                'description' => 'Cacao oscuro, leche texturizada y vainilla.',
                'price' => 4.00,
                'position' => 8,
            ],
            [
                'category_key' => 'desayunos',
                'name' => 'Chai Latte',
                'description' => 'Chai especiado con leche vaporizada.',
                'price' => 5.00,
                'position' => 9,
            ],
            [
                'category_key' => 'desayunos',
                'name' => 'Tostadas con mantequilla',
                'description' => 'Pan artesanal tostado con mantequilla batida.',
                'price' => 4.50,
                'position' => 10,
            ],
            [
                'category_key' => 'desayunos',
                'name' => 'Mallorca',
                'description' => 'Mallorca clásica con mantequilla y azúcar glas.',
                'price' => 4.50,
                'position' => 11,
            ],
            [
                'category_key' => 'desayunos',
                'name' => 'Revoltillo',
                'description' => 'Huevos suaves con hierbas y aceite de oliva.',
                'price' => 8.50,
                'position' => 12,
            ],
            [
                'category_key' => 'desayunos',
                'name' => 'Avena con frutas y granola',
                'description' => 'Avena cremosa con toppings de fruta fresca.',
                'price' => 5.00,
                'position' => 13,
            ],
            [
                'category_key' => 'desayunos',
                'name' => 'Avena plain',
                'description' => 'Versión clásica con toque de canela.',
                'price' => 4.00,
                'position' => 14,
            ],

            // Brunch
            [
                'category_key' => 'brunch',
                'name' => 'Cranberry French Toast',
                'description' => 'Brioche bañado en syrup de cranberry con frutos rojos.',
                'price' => 12.00,
                'position' => 1,
                'featured_on_cover' => true,
            ],
            [
                'category_key' => 'brunch',
                'name' => 'Triple Decker Montecristo',
                'description' => 'French toast relleno de jamón, queso y miel especiada.',
                'price' => 12.00,
                'position' => 2,
            ],
            [
                'category_key' => 'brunch',
                'name' => 'Coquito French Toast',
                'description' => 'Toasts infusionadas en coquito artesanal y coco tostado.',
                'price' => 12.00,
                'position' => 3,
            ],
            [
                'category_key' => 'brunch',
                'name' => '50/50 French Toast',
                'description' => 'Mitad dulce, mitad salado con sirope de ron especiado.',
                'price' => 14.00,
                'position' => 4,
            ],
            [
                'category_key' => 'brunch',
                'name' => 'Waffle con frutas, Nutella & whip',
                'description' => 'Waffle dorado con frutas de temporada, Nutella y crema.',
                'price' => 10.00,
                'position' => 5,
            ],
            [
                'category_key' => 'brunch',
                'name' => 'Pumpkin Cheesecake French Toast',
                'description' => 'Relleno de cheesecake de calabaza y crumble especiado.',
                'price' => 12.00,
                'position' => 6,
            ],
            [
                'category_key' => 'brunch',
                'name' => 'Berry Cheesecake Waffle',
                'description' => 'Waffle crujiente con cheesecake batido y berries.',
                'price' => 12.00,
                'position' => 7,
            ],
            [
                'category_key' => 'brunch',
                'name' => 'French Marqués',
                'description' => 'French toast deluxe con helado y reducción de Aperol.',
                'price' => 16.00,
                'position' => 8,
            ],
            [
                'category_key' => 'brunch',
                'name' => 'Happy Wife Happy Life',
                'description' => 'Tabla brunch para compartir con pancakes, frutas y salsas.',
                'price' => 15.00,
                'position' => 9,
            ],

            // Sandwiches
            [
                'category_key' => 'sandwiches',
                'name' => 'Sandwich (J Q H)',
                'description' => 'Jamón, queso y huevo con pan sobao a la plancha.',
                'price' => 7.50,
                'position' => 1,
            ],
            [
                'category_key' => 'sandwiches',
                'name' => 'Sandwich (J - Q)',
                'description' => 'Sándwich clásico de jamón y queso tostado.',
                'price' => 6.50,
                'position' => 2,
            ],
            [
                'category_key' => 'sandwiches',
                'name' => 'Sandwich de hummus',
                'description' => 'Hummus casero, vegetales asados y pan ciabatta.',
                'price' => 10.00,
                'position' => 3,
            ],
            [
                'category_key' => 'sandwiches',
                'name' => 'Sandwich italiano',
                'description' => 'Capicola, mozzarella fresca, pesto y tomate heirloom.',
                'price' => 10.00,
                'position' => 4,
            ],
            [
                'category_key' => 'sandwiches',
                'name' => 'Mediterranean Wrap',
                'description' => 'Wrap tibio con pollo, tzatziki y vegetales crujientes.',
                'price' => 11.00,
                'position' => 5,
            ],
            [
                'category_key' => 'sandwiches',
                'name' => 'Cordon Bleu',
                'description' => 'Pechuga empanada rellena de jamón y queso derretido.',
                'price' => 10.00,
                'position' => 6,
            ],
            [
                'category_key' => 'sandwiches',
                'name' => 'Sandwich J-Q (Clásico)',
                'description' => 'Receta insignia con pan sobao y mantequilla local.',
                'price' => 6.50,
                'position' => 7,
            ],

            // Postres y otros
            [
                'category_key' => 'postres',
                'name' => 'Choco Chip + Almond Cheesecake Waffle',
                'description' => 'Waffle relleno de cheesecake de almendra y chispas de chocolate.',
                'price' => 12.00,
                'position' => 1,
            ],
            [
                'category_key' => 'postres',
                'name' => 'Iced Chocolate',
                'description' => 'Bebida fría de cacao oscuro con leche especiada.',
                'price' => 4.50,
                'position' => 2,
            ],
            [
                'category_key' => 'postres',
                'name' => 'Iced Mocha',
                'description' => 'Versión fría del mocha con espresso doble.',
                'price' => 5.50,
                'position' => 3,
            ],
            [
                'category_key' => 'postres',
                'name' => 'Iced Chai Latte',
                'description' => 'Infusión fría de chai con leche especiada.',
                'price' => 5.50,
                'position' => 4,
            ],
            [
                'category_key' => 'postres',
                'name' => 'Refrescos',
                'description' => 'Selección de refrescos artesanales y sodas.',
                'price' => 1.75,
                'position' => 5,
            ],
            [
                'category_key' => 'postres',
                'name' => 'Water',
                'description' => 'Agua filtrada o con gas.',
                'price' => 1.75,
                'position' => 6,
            ],

            // Menú de chitines
            [
                'category_key' => 'chitines',
                'name' => '1/2 Pancake (Frutas, Nutella y whip)',
                'description' => 'Media porción pensada para peques con toppings.',
                'price' => 6.00,
                'position' => 1,
            ],
            [
                'category_key' => 'chitines',
                'name' => 'Pedacitos',
                'description' => 'Bocados dulces para compartir.',
                'price' => 4.00,
                'position' => 2,
            ],
            [
                'category_key' => 'chitines',
                'name' => 'Side - Tortilla',
                'description' => 'Porción individual de tortilla española.',
                'price' => 6.00,
                'position' => 3,
            ],
            [
                'category_key' => 'chitines',
                'name' => 'Side - Revoltillo',
                'description' => 'Huevos batidos con sofrito ahumado.',
                'price' => 6.00,
                'position' => 4,
            ],
            [
                'category_key' => 'chitines',
                'name' => 'Agua',
                'description' => 'Agua filtrada en porción infantil.',
                'price' => 1.75,
                'position' => 5,
            ],
        ];

        foreach ($dishes as $dish) {
            if (!isset($categoryIds[$dish['category_key']])) {
                continue;
            }

            Dish::updateOrCreate(
                ['name' => $dish['name']],
                [
                    'description' => $dish['description'],
                    'price' => $dish['price'],
                    'category_id' => $categoryIds[$dish['category_key']],
                    'image' => $dish['image'] ?? null,
                    'visible' => $dish['visible'] ?? true,
                    'featured_on_cover' => $dish['featured_on_cover'] ?? false,
                    'position' => $dish['position'],
                ]
            );
        }
    }
}
