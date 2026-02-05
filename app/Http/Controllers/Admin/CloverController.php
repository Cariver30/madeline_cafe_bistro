<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CloverCategory;
use App\Models\CocktailCategory;
use App\Models\CantinaCategory;
use App\Models\Setting;
use App\Models\WineCategory;
use App\Support\CloverClient;
use App\Support\CloverSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use RuntimeException;

class CloverController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $settings = Setting::first();
        $cloverCategories = CloverCategory::orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $viewLabels = [
            'menu' => trim($settings->tab_label_menu ?? $settings->button_label_menu ?? 'Menú'),
            'cocktails' => trim($settings->tab_label_cocktails ?? $settings->button_label_cocktails ?? 'Cócteles'),
            'wines' => trim($settings->tab_label_wines ?? $settings->button_label_wines ?? 'Café & Brunch'),
            'cantina' => trim($settings->tab_label_cantina ?? $settings->button_label_cantina ?? 'Cantina'),
        ];
        $parentOptions = [
            'menu' => Category::orderBy('order')->orderBy('id')->get(),
            'cocktails' => CocktailCategory::orderBy('order')->orderBy('id')->get(),
            'wines' => WineCategory::orderBy('order')->orderBy('id')->get(),
            'cantina' => CantinaCategory::orderBy('order')->orderBy('id')->get(),
        ];

        $topSellers = null;
        $fromValue = $request->input('from');
        $toValue = $request->input('to');

        if ($request->boolean('top_sellers')) {
            $client = $this->resolveClient($request);
            if (! $client) {
                return redirect()->route('admin.clover.index')
                    ->with('error', 'Configura las credenciales de Clover antes de consultar ventas.');
            }

            try {
                $from = $fromValue ? Carbon::parse($fromValue) : now()->subDays(6);
                $to = $toValue ? Carbon::parse($toValue) : now();
                $syncService = new CloverSyncService($client);
                $topSellers = $syncService->topSellers($from, $to, 10);
            } catch (RuntimeException $exception) {
                return redirect()->route('admin.clover.index')
                    ->with('error', 'No se pudieron consultar las ventas. ' . $exception->getMessage());
            }
        }

        return view('admin.clover', [
            'settings' => $settings,
            'cloverCategories' => $cloverCategories,
            'viewLabels' => $viewLabels,
            'parentOptions' => $parentOptions,
            'topSellers' => $topSellers,
            'fromValue' => $fromValue,
            'toValue' => $toValue,
        ]);
    }

    public function test(Request $request)
    {
        $this->authorizeAdmin();

        $client = $this->resolveClient($request);
        if (! $client) {
            return back()->with('error', 'Configura el Merchant ID y el access token de Clover antes de probar.');
        }

        try {
            $syncService = new CloverSyncService($client);
            $count = $syncService->syncCategories();
        } catch (RuntimeException $exception) {
            return back()->with('error', 'No se pudo conectar con Clover. ' . $exception->getMessage());
        }

        return back()->with('success', "Conexión exitosa. Categorías sincronizadas: {$count}.");
    }

    public function syncCategories(Request $request)
    {
        $this->authorizeAdmin();

        $client = $this->resolveClient($request);
        if (! $client) {
            return back()->with('error', 'Configura las credenciales de Clover antes de sincronizar.');
        }

        try {
            $syncService = new CloverSyncService($client);
            $count = $syncService->syncCategories();
        } catch (RuntimeException $exception) {
            return back()->with('error', 'Error al sincronizar categorías: ' . $exception->getMessage());
        }

        return back()->with('success', "Categorías Clover actualizadas: {$count}.");
    }

    public function syncItems(Request $request)
    {
        $this->authorizeAdmin();
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $client = $this->resolveClient($request);
        if (! $client) {
            return back()->with('error', 'Configura las credenciales de Clover antes de importar.');
        }

        try {
            $syncService = new CloverSyncService($client);
            $syncTaxes = $request->boolean('sync_taxes');
            $count = $syncService->syncItems($syncTaxes);
        } catch (RuntimeException $exception) {
            return back()->with('error', 'Error al importar items: ' . $exception->getMessage());
        }

        return back()->with('success', "Items Clover importados: {$count}.");
    }

    public function syncAll(Request $request)
    {
        $this->authorizeAdmin();
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $client = $this->resolveClient($request);
        if (! $client) {
            return back()->with('error', 'Configura las credenciales de Clover antes de sincronizar.');
        }

        try {
            $syncService = new CloverSyncService($client);
            $categoriesCount = $syncService->syncCategories();
            $syncTaxes = $request->boolean('sync_taxes');
            $itemsCount = $syncService->syncItems($syncTaxes);
        } catch (RuntimeException $exception) {
            return back()->with('error', 'Error al sincronizar Clover: ' . $exception->getMessage());
        }

        return back()->with('success', "Clover sincronizado. Categorías: {$categoriesCount}. Items: {$itemsCount}.");
    }

    public function updateScopes(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'scopes' => ['nullable', 'array'],
            'scopes.*' => ['nullable', 'in:menu,cocktails,wines,cantina'],
            'parent_categories' => ['nullable', 'array'],
            'parent_categories.*' => ['nullable', 'integer'],
        ]);

        $scopes = Arr::get($data, 'scopes', []);
        $parents = Arr::get($data, 'parent_categories', []);

        foreach ($scopes as $categoryId => $scope) {
            $category = CloverCategory::find($categoryId);
            if (! $category) {
                continue;
            }

            $originalScope = $category->scope;
            $originalParent = $category->parent_category_id;
            $newScope = $scope ?: null;
            $parentId = $parents[$categoryId] ?? null;

            $category->scope = $newScope;
            $category->parent_category_id = $this->resolveParentCategoryId($newScope, $parentId);

            if ($originalScope !== $category->scope || $originalParent !== $category->parent_category_id) {
                $category->subcategory_id = null;
            }

            $category->save();
        }

        return back()->with('success', 'Mapeo de categorías actualizado.');
    }

    public function storeParentCategory(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'parent_scope' => ['required', 'in:menu,cocktails,wines,cantina'],
            'parent_name' => ['required', 'string', 'max:255'],
        ]);

        $name = trim($data['parent_name']);
        $scope = $data['parent_scope'];

        $created = match ($scope) {
            'menu' => Category::create([
                'name' => $name,
                'show_on_cover' => false,
            ]),
            'cocktails' => CocktailCategory::create([
                'name' => $name,
                'show_on_cover' => false,
            ]),
            'wines' => WineCategory::create([
                'name' => $name,
                'show_on_cover' => false,
            ]),
            'cantina' => CantinaCategory::create([
                'name' => $name,
                'show_on_cover' => false,
            ]),
            default => null,
        };

        if (! $created) {
            return back()->with('error', 'No se pudo crear la categoría interna.');
        }

        return back()->with('success', 'Categoría interna creada. Ya puedes usarla en el mapeo.');
    }

    private function resolveClient(Request $request): ?CloverClient
    {
        $settings = Setting::first();

        return CloverClient::fromSettings($settings, [
            'merchant_id' => $request->input('clover_merchant_id'),
            'access_token' => $request->input('clover_access_token'),
            'environment' => $request->input('clover_env'),
        ]);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    private function resolveParentCategoryId(?string $scope, $parentId): ?int
    {
        if (! $scope || ! $parentId) {
            return null;
        }

        $id = (int) $parentId;

        return match ($scope) {
            'menu' => Category::whereKey($id)->exists() ? $id : null,
            'cocktails' => CocktailCategory::whereKey($id)->exists() ? $id : null,
            'wines' => WineCategory::whereKey($id)->exists() ? $id : null,
            'cantina' => CantinaCategory::whereKey($id)->exists() ? $id : null,
            default => null,
        };
    }
}
