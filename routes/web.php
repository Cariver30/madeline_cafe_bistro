<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategorySubcategoryController;
use App\Http\Controllers\ExtraController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\TableOrderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CocktailController;
use App\Http\Controllers\CocktailCategoryController;
use App\Http\Controllers\CocktailSubcategoryController;
use App\Http\Controllers\WineCategoryController;
use App\Http\Controllers\WineController;
use App\Http\Controllers\WineSubcategoryController;
use App\Http\Controllers\CantinaController;
use App\Http\Controllers\CantinaCategoryController;
use App\Http\Controllers\CantinaItemController;
use App\Http\Controllers\SpecialPublicController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\FoodPairingController;
use App\Http\Controllers\WineTypeController;
use App\Http\Controllers\Admin\EventManagementController;
use App\Http\Controllers\EventPublicController;
use App\Http\Controllers\EventNotificationController;
use App\Http\Controllers\Admin\EventPromotionController;
use App\Http\Controllers\Admin\LoyaltyAdminController;
use App\Http\Controllers\Admin\CloverController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\PrinterAdminController;
use App\Http\Controllers\Admin\PrepAreaAdminController;
use App\Http\Controllers\Admin\PrepLabelAdminController;
use App\Http\Controllers\Admin\TaxAdminController;
use App\Http\Controllers\Admin\DiningTableAdminController;
use App\Http\Controllers\Admin\SpecialController;
use App\Http\Controllers\Loyalty\InvitationController;
use App\Http\Controllers\Loyalty\RedemptionController;
use App\Http\Controllers\Loyalty\ServerDashboardController;
use App\Http\Controllers\Loyalty\VisitConfirmationController;
use App\Http\Controllers\CloudPrntController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\OnlineOrderController;
use App\Http\Controllers\WaitingListPublicController;

use App\Http\Controllers\HomeController;

// Rutas públicas
Route::get('/', [HomeController::class, 'cover'])->name('cover');
Route::get('/menu', [MenuController::class, 'index'])->name('menu');
Route::view('/privacidad', 'privacy')->name('privacy');
Route::view('/privacy', 'privacy');
Route::get('/ordenar', [OnlineOrderController::class, 'show'])->name('online.order.show');
Route::post('/ordenar/checkout', [OnlineOrderController::class, 'checkout'])->name('online.order.checkout');
Route::get('/ordenar/checkout/{token}', [OnlineOrderController::class, 'checkoutPage'])->name('online.order.checkout.page');
Route::get('/ordenar/resultado/{status}/{token}', [OnlineOrderController::class, 'result'])->name('online.order.result');
Route::get('/cantina', [CantinaController::class, 'index'])->name('cantina.index');
Route::get('/especiales', [SpecialPublicController::class, 'index'])->name('specials.index');
Route::get('/mesa/{token}', [TableOrderController::class, 'show'])->name('table.order.show');
Route::post('/mesa/{token}/orders', [TableOrderController::class, 'store'])->name('table.order.store');
Route::get('/lista-de-espera', [WaitingListPublicController::class, 'show'])->name('waiting-list.show');
Route::post('/lista-de-espera', [WaitingListPublicController::class, 'store'])->name('waiting-list.store');
Route::get('/receipts/pos/{order}', [ReceiptController::class, 'pos'])
    ->middleware('signed')
    ->name('receipts.pos.download');
Route::prefix('cloudprnt')->name('cloudprnt.')->group(function () {
    Route::get('/{printer:token}/poll', [CloudPrntController::class, 'poll'])->name('poll');
    Route::get('/{printer:token}/jobs/{job}', [CloudPrntController::class, 'job'])->name('job');
    Route::post('/{printer:token}/jobs/{job}/ack', [CloudPrntController::class, 'ack'])->name('ack');
});
Route::get('/cocktails', [CocktailController::class, 'index'])->name('cocktails.index');
Route::get('/coffee', [WineController::class, 'index'])->name('coffee.index');
Route::redirect('/wines', '/coffee');
Route::get('/reservations', function () {
    return redirect()->away('https://asador-1293f.web.app/');
})->name('reservations.app');
Route::resource('categories', CategoryController::class);
Route::resource('dishes', DishController::class);
Route::resource('cocktails', CocktailController::class);
Route::resource('cantina-items', CantinaItemController::class)->except(['index', 'show']);
Route::resource('cantina-categories', CantinaCategoryController::class)->except(['show']);
Route::resource('cocktail-categories', CocktailCategoryController::class);
Route::post('/admin/dishes/reorder', [DishController::class, 'reorder'])->name('dishes.reorder');
Route::post('/admin/cocktails/reorder', [CocktailController::class, 'reorder'])->name('cocktails.reorder');
Route::post('/admin/wines/reorder', [WineController::class, 'reorder'])->name('wines.reorder');
Route::post('/admin/cantina/reorder', [CantinaItemController::class, 'reorder'])->name('cantina.reorder');

Route::prefix('experiencias')->name('experiences.')->group(function () {
    Route::get('/', [EventPublicController::class, 'index'])->name('index');
    Route::get('/{event:slug}', [EventPublicController::class, 'show'])->name('show');
    Route::post('/{event:slug}/tickets', [EventPublicController::class, 'purchase'])->name('purchase');
    Route::post('/registro', [EventNotificationController::class, 'subscribeGeneral'])->name('notify.general');
    Route::post('/registro/cover', [EventNotificationController::class, 'subscribeFromCover'])->name('notify.cover');
    Route::post('/{event:slug}/notify', [EventNotificationController::class, 'subscribe'])->name('notify');
});

Route::middleware(['auth', 'role:admin,manager'])->group(function () {
    Route::redirect('/admin/panel', '/admin/new-panel')->name('admin.panel');
    Route::redirect('/admin', '/admin/new-panel')->name('admin');
    Route::get('/admin/categories', [CategoryController::class, 'showCategories'])->name('admin.categories');
    Route::get('/admin/new-panel', [AdminController::class, 'newAdminPanel'])->name('admin.new-panel');

    Route::post('/update-category-order', [CategoryController::class, 'updateOrder'])->name('categories.reorder');
    Route::get('/categories/json', [CategoryController::class, 'getCategoriesJson']);

    Route::prefix('admin/events')->name('admin.events.')->group(function () {
        Route::get('/', [EventManagementController::class, 'index'])->name('index');
        Route::get('/create', [EventManagementController::class, 'create'])->name('create');
        Route::post('/', [EventManagementController::class, 'store'])->name('store');
        Route::get('/{event}/edit', [EventManagementController::class, 'edit'])->name('edit');
        Route::put('/{event}', [EventManagementController::class, 'update'])->name('update');
        Route::delete('/{event}', [EventManagementController::class, 'destroy'])->name('destroy');

        Route::post('/{event}/sections', [EventManagementController::class, 'storeSection'])->name('sections.store');
        Route::delete('/sections/{section}', [EventManagementController::class, 'destroySection'])->name('sections.destroy');

        Route::get('/notifications', [EventNotificationController::class, 'index'])->name('notifications');

        Route::get('/promotions', [EventPromotionController::class, 'index'])->name('promotions.index');
        Route::get('/promotions/create', [EventPromotionController::class, 'create'])->name('promotions.create');
        Route::post('/promotions', [EventPromotionController::class, 'store'])->name('promotions.store');
    });

    Route::prefix('admin/specials')->name('admin.specials.')->group(function () {
        Route::get('/', [SpecialController::class, 'index'])->name('index');
        Route::get('/create', [SpecialController::class, 'create'])->name('create');
        Route::post('/', [SpecialController::class, 'store'])->name('store');
        Route::get('/{special}/edit', [SpecialController::class, 'edit'])->name('edit');
        Route::put('/{special}', [SpecialController::class, 'update'])->name('update');
        Route::delete('/{special}', [SpecialController::class, 'destroy'])->name('destroy');
    });

    Route::patch('dishes/{dish}/toggle-visibility', [DishController::class, 'toggleVisibility'])->name('dishes.toggleVisibility');
    Route::patch('dishes/{dish}/toggle-featured', [DishController::class, 'toggleFeatured'])->name('dishes.toggleFeatured');
    Route::patch('cocktails/{cocktail}/toggle-visibility', [CocktailController::class, 'toggleVisibility'])->name('cocktails.toggleVisibility');
    Route::patch('cocktails/{cocktail}/toggle-featured', [CocktailController::class, 'toggleFeatured'])->name('cocktails.toggleFeatured');
    Route::patch('wines/{wine}/toggle-visibility', [WineController::class, 'toggleVisibility'])->name('wines.toggleVisibility');
    Route::patch('wines/{wine}/toggle-featured', [WineController::class, 'toggleFeatured'])->name('wines.toggleFeatured');
    Route::patch('cantina-items/{cantinaItem}/toggle-visibility', [CantinaItemController::class, 'toggleVisibility'])->name('cantina-items.toggleVisibility');
    Route::patch('cantina-items/{cantinaItem}/toggle-featured', [CantinaItemController::class, 'toggleFeatured'])->name('cantina-items.toggleFeatured');

    Route::post('/admin/dishes/reorder', [DishController::class, 'reorder'])->name('dishes.reorder');
    Route::post('/admin/cocktails/reorder', [CocktailController::class, 'reorder'])->name('cocktails.reorder');
    Route::post('/admin/wines/reorder', [WineController::class, 'reorder'])->name('wines.reorder');
    Route::post('/admin/cantina/reorder', [CantinaItemController::class, 'reorder'])->name('cantina.reorder');

    Route::prefix('admin/tables')->name('admin.tables.')->group(function () {
        Route::post('/', [DiningTableAdminController::class, 'store'])->name('store');
        Route::patch('/{diningTable}', [DiningTableAdminController::class, 'update'])->name('update');
        Route::patch('/{diningTable}/status', [DiningTableAdminController::class, 'updateStatus'])->name('status');
        Route::delete('/{diningTable}', [DiningTableAdminController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin/printers')->name('admin.printers.')->group(function () {
        Route::post('/', [PrinterAdminController::class, 'storePrinter'])->name('store');
        Route::patch('/{printer}', [PrinterAdminController::class, 'updatePrinter'])->name('update');
        Route::delete('/{printer}', [PrinterAdminController::class, 'destroyPrinter'])->name('destroy');

        Route::post('/templates', [PrinterAdminController::class, 'storeTemplate'])->name('templates.store');
        Route::patch('/templates/{printTemplate}', [PrinterAdminController::class, 'updateTemplate'])->name('templates.update');
        Route::delete('/templates/{printTemplate}', [PrinterAdminController::class, 'destroyTemplate'])->name('templates.destroy');

        Route::post('/routes', [PrinterAdminController::class, 'storeRoute'])->name('routes.store');
        Route::delete('/routes/{printerRoute}', [PrinterAdminController::class, 'destroyRoute'])->name('routes.destroy');
    });

    Route::prefix('admin/taxes')->name('admin.taxes.')->group(function () {
        Route::post('/', [TaxAdminController::class, 'store'])->name('store');
        Route::patch('/{tax}', [TaxAdminController::class, 'update'])->name('update');
        Route::delete('/{tax}', [TaxAdminController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin/prep')->name('admin.prep.')->group(function () {
        Route::post('/areas', [PrepAreaAdminController::class, 'store'])->name('areas.store');
        Route::patch('/areas/{prepArea}', [PrepAreaAdminController::class, 'update'])->name('areas.update');
        Route::delete('/areas/{prepArea}', [PrepAreaAdminController::class, 'destroy'])->name('areas.destroy');

        Route::post('/labels', [PrepLabelAdminController::class, 'store'])->name('labels.store');
        Route::patch('/labels/{prepLabel}', [PrepLabelAdminController::class, 'update'])->name('labels.update');
        Route::delete('/labels/{prepLabel}', [PrepLabelAdminController::class, 'destroy'])->name('labels.destroy');
    });
    Route::post('/admin/cocktail-categories/reorder', [CocktailCategoryController::class, 'reorder'])->name('cocktail-categories.reorder');
    Route::post('/admin/wine-categories/reorder', [WineCategoryController::class, 'reorder'])->name('wine-categories.reorder');
    Route::post('/admin/cantina-categories/reorder', [CantinaCategoryController::class, 'reorder'])->name('cantina-categories.reorder');
    Route::patch('/admin/categories/{category}/toggle-cover', [CategoryController::class, 'toggleCover'])->name('categories.toggleCover');
    Route::patch('/admin/cocktail-categories/{cocktailCategory}/toggle-cover', [CocktailCategoryController::class, 'toggleCover'])->name('cocktail-categories.toggleCover');
    Route::patch('/admin/wine-categories/{wineCategory}/toggle-cover', [WineCategoryController::class, 'toggleCover'])->name('wine-categories.toggleCover');
    Route::patch('/admin/cantina-categories/{cantinaCategory}/toggle-cover', [CantinaCategoryController::class, 'toggleCover'])->name('cantina-categories.toggleCover');
    Route::post('/admin/categories/{category}/featured-items', [CategoryController::class, 'updateFeaturedItems'])->name('categories.featuredItems');
    Route::post('/admin/cocktail-categories/{cocktailCategory}/featured-items', [CocktailCategoryController::class, 'updateFeaturedItems'])->name('cocktail-categories.featuredItems');
    Route::post('/admin/wine-categories/{wineCategory}/featured-items', [WineCategoryController::class, 'updateFeaturedItems'])->name('wine-categories.featuredItems');
    Route::post('/admin/cantina-categories/{cantinaCategory}/featured-items', [CantinaCategoryController::class, 'updateFeaturedItems'])->name('cantina-categories.featuredItems');
    Route::post('/admin/categories/{category}/subcategories', [CategorySubcategoryController::class, 'store'])->name('category-subcategories.store');
    Route::patch('/admin/subcategories/{subcategory}', [CategorySubcategoryController::class, 'update'])->name('category-subcategories.update');
    Route::delete('/admin/subcategories/{subcategory}', [CategorySubcategoryController::class, 'destroy'])->name('category-subcategories.destroy');
    Route::post('/admin/cocktail-categories/{cocktailCategory}/subcategories', [CocktailSubcategoryController::class, 'store'])->name('cocktail-subcategories.store');
    Route::patch('/admin/cocktail-subcategories/{subcategory}', [CocktailSubcategoryController::class, 'update'])->name('cocktail-subcategories.update');
    Route::delete('/admin/cocktail-subcategories/{subcategory}', [CocktailSubcategoryController::class, 'destroy'])->name('cocktail-subcategories.destroy');
    Route::post('/admin/wine-categories/{wineCategory}/subcategories', [WineSubcategoryController::class, 'store'])->name('wine-subcategories.store');
    Route::patch('/admin/wine-subcategories/{subcategory}', [WineSubcategoryController::class, 'update'])->name('wine-subcategories.update');
    Route::delete('/admin/wine-subcategories/{subcategory}', [WineSubcategoryController::class, 'destroy'])->name('wine-subcategories.destroy');

    Route::resource('wine-categories', WineCategoryController::class);
    Route::resource('wines', WineController::class)->except(['index']);
    Route::resource('wine-types', WineTypeController::class);
    Route::resource('regions', App\Http\Controllers\RegionController::class);
    Route::resource('food-pairings', FoodPairingController::class);
    Route::resource('grapes', App\Http\Controllers\GrapeController::class);
    Route::resource('extras', ExtraController::class)->except(['show', 'create']);

    Route::get('/admin/popups', [AdminController::class, 'indexPopups'])->name('admin.popups.index');
    Route::get('/admin/popups/create', [AdminController::class, 'createPopup'])->name('admin.popups.create');
    Route::post('/admin/popups', [AdminController::class, 'storePopup'])->name('admin.popups.store');
    Route::get('/admin/popups/{popup}/edit', [AdminController::class, 'editPopup'])->name('admin.popups.edit');
    Route::put('/admin.popups/{popup}', [AdminController::class, 'updatePopup'])->name('admin.popups.update');
    Route::delete('/admin.popups/{popup}', [AdminController::class, 'destroyPopup'])->name('admin.popups.destroy');
    Route::patch('/admin/popups/{popup}/toggle-visibility', [AdminController::class, 'toggleVisibility'])->name('admin.popups.toggleVisibility');

    Route::post('/admin/contact-info', [AdminController::class, 'updateContactInfo'])->name('admin.contact-info.update');

    Route::post('/admin/loyalty/settings', [LoyaltyAdminController::class, 'updateSettings'])->name('admin.loyalty.settings');
    Route::post('/admin/loyalty/rewards', [LoyaltyAdminController::class, 'storeReward'])->name('admin.loyalty.rewards.store');
    Route::put('/admin/loyalty/rewards/{loyaltyReward}', [LoyaltyAdminController::class, 'updateReward'])->name('admin.loyalty.rewards.update');
    Route::patch('/admin/loyalty/rewards/{loyaltyReward}/toggle', [LoyaltyAdminController::class, 'toggleReward'])->name('admin.loyalty.rewards.toggle');
    Route::delete('/admin/loyalty/rewards/{loyaltyReward}', [LoyaltyAdminController::class, 'destroyReward'])->name('admin.loyalty.rewards.destroy');
    Route::post('/admin/loyalty/servers', [LoyaltyAdminController::class, 'storeServer'])->name('admin.loyalty.servers.store');
    Route::post('/admin/loyalty/servers/{user}/resend', [LoyaltyAdminController::class, 'resendInvitation'])->name('admin.loyalty.servers.resend');
    Route::patch('/admin/loyalty/servers/{user}/toggle', [LoyaltyAdminController::class, 'toggleServer'])->name('admin.loyalty.servers.toggle');
    Route::delete('/admin/loyalty/servers/{user}', [LoyaltyAdminController::class, 'destroyServer'])->name('admin.loyalty.servers.destroy');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/settings/edit', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/admin/settings/update', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/admin/update-background', [AdminController::class, 'updateBackground'])->name('admin.updateBackground');
    Route::get('/admin/clover', [CloverController::class, 'index'])->name('admin.clover.index');
    Route::post('/admin/clover/test', [CloverController::class, 'test'])->name('admin.clover.test');
    Route::post('/admin/clover/categories/sync', [CloverController::class, 'syncCategories'])->name('admin.clover.categories.sync');
    Route::post('/admin/clover/items/sync', [CloverController::class, 'syncItems'])->name('admin.clover.items.sync');
    Route::post('/admin/clover/sync-all', [CloverController::class, 'syncAll'])->name('admin.clover.sync_all');
    Route::post('/admin/clover/parent-categories', [CloverController::class, 'storeParentCategory'])->name('admin.clover.parent-categories.store');
    Route::post('/admin/clover/scopes', [CloverController::class, 'updateScopes'])->name('admin.clover.scopes.update');
    Route::post('/admin/managers', [UserManagementController::class, 'storeManager'])->name('admin.managers.store');
    Route::patch('/admin/managers/{user}/toggle', [UserManagementController::class, 'toggleManager'])->name('admin.managers.toggle');
    Route::patch('/admin/managers/{user}/resend', [UserManagementController::class, 'resendInvitation'])->name('admin.managers.resend');
    Route::delete('/admin/managers/{user}', [UserManagementController::class, 'destroyManager'])->name('admin.managers.destroy');
});

Route::middleware(['auth', 'role:server'])->group(function () {
    Route::get('/loyalty/dashboard', [ServerDashboardController::class, 'index'])->name('loyalty.dashboard');
    Route::post('/loyalty/visits', [ServerDashboardController::class, 'storeVisit'])->name('loyalty.visit.create');
    Route::post('/loyalty/customers/lookup', [ServerDashboardController::class, 'lookupCustomer'])->name('loyalty.customer.lookup');
});

Route::middleware(['auth', 'role:server,pos,manager'])->group(function () {
    Route::get('/loyalty/redeem/{token}', [RedemptionController::class, 'show'])->name('loyalty.redeem.show');
    Route::post('/loyalty/redeem/{token}', [RedemptionController::class, 'store'])->name('loyalty.redeem.store');
});

Route::get('/loyalty/check-in/{token}', [VisitConfirmationController::class, 'show'])->name('loyalty.visit.show');
Route::post('/loyalty/check-in/{token}', [VisitConfirmationController::class, 'store'])->name('loyalty.visit.store');
Route::get('/loyalty/gracias', [VisitConfirmationController::class, 'thanks'])->name('loyalty.confirm.thanks');

Route::get('/loyalty/invitations', [InvitationController::class, 'show'])->name('loyalty.invitations.show');
Route::post('/loyalty/invitations', [InvitationController::class, 'store'])->name('loyalty.invitations.store');

// Rutas protegidas para usuarios autenticados sin middleware
Route::get('/dashboard', function () {
    $user = auth()->user();
    if (! $user) {
        return redirect()->route('login');
    }

    if ($user->isServer()) {
        return redirect()->route('loyalty.dashboard');
    }

    return redirect()->route('admin.new-panel');
})->middleware(['auth'])->name('dashboard');

Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

// Rutas de autenticación
require __DIR__.'/auth.php';

Route::get('/home', function () {
    return redirect()->route('cover');
})->name('home');
