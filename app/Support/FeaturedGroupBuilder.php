<?php

namespace App\Support;

use App\Models\Category;
use App\Models\CocktailCategory;
use App\Models\WineCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FeaturedGroupBuilder
{
    /**
     * Build the data structure used by the cover and admin panel.
     */
    public static function build(bool $includeEmpty = false): Collection
    {
        $groups = collect();

        $sources = [
            [
                'type' => 'menu',
                'label' => 'Menú',
                'query' => Category::query(),
                'relation' => 'dishes',
            ],
            [
                'type' => 'cocktails',
                'label' => 'Cócteles',
                'query' => CocktailCategory::query(),
                'relation' => 'items',
            ],
            [
                'type' => 'coffee',
                'label' => 'Café',
                'query' => WineCategory::query(),
                'relation' => 'items',
            ],
        ];

        foreach ($sources as $source) {
            $categories = $source['query']
                ->where('show_on_cover', true)
                ->orderBy('order')
                ->with([$source['relation'] => function ($query) {
                    $query->where('visible', true)
                        ->where('featured_on_cover', true)
                        ->orderBy('position')
                        ->orderBy('id');
                }])
                ->get();

            foreach ($categories as $category) {
                $items = $category->{$source['relation']}->map(function ($item) use ($source) {
                    return [
                        'title' => $item->name,
                        'subtitle' => $item->description,
                        'price' => $item->price,
                        'image' => self::itemImage($item),
                        'link' => self::itemLink($item, $source['type']),
                    ];
                })->values();

                if (!$includeEmpty && $items->isEmpty()) {
                    continue;
                }

                $groups->push([
                    'slug' => Str::slug(($category->cover_title ?: $category->name) . '-' . $category->id),
                    'title' => $category->cover_title ?: $category->name,
                    'subtitle' => $category->cover_subtitle,
                    'source' => $source['type'],
                    'source_label' => $source['label'],
                    'items' => $items,
                ]);
            }
        }

        return $groups;
    }

    protected static function itemImage($item): ?string
    {
        if (!empty($item->image)) {
            return asset('storage/' . $item->image);
        }

        return null;
    }

    protected static function itemLink($item, string $source): string
    {
        return match ($source) {
            'cocktails' => url('/cocktails') . '#cocktail' . $item->id,
            'coffee' => url('/coffee') . '#coffee' . $item->id,
            default => url('/menu') . '#dish' . $item->id,
        };
    }
}
