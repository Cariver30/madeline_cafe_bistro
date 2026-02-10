<?php

use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\CocktailManagementController;
use App\Http\Controllers\Api\DiningTableController;
use App\Http\Controllers\Api\WaitingListController;
use Illuminate\Broadcasting\BroadcastController;
use App\Http\Controllers\Api\TwilioWebhookController;
use App\Http\Controllers\Api\ExtraManagementController;
use App\Http\Controllers\Api\CantinaManagementController;
use App\Http\Controllers\Api\ManagerDashboardController;
use App\Http\Controllers\Api\MenuManagementController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileViewSettingsController;
use App\Http\Controllers\Api\KitchenAreaController;
use App\Http\Controllers\Api\KitchenOrderController;
use App\Http\Controllers\Api\LoyaltyRedemptionController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\PaymentManagementController;
use App\Http\Controllers\Api\PosTicketController;
use App\Http\Controllers\Api\PrepAreaManagementController;
use App\Http\Controllers\Api\PrepLabelManagementController;
use App\Http\Controllers\Api\StripeTerminalController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\ServerDashboardController;
use App\Http\Controllers\Api\ServerOrderController;
use App\Http\Controllers\Api\ServerMenuController;
use App\Http\Controllers\Api\ServerTableSessionController;
use App\Http\Controllers\Api\ServerVisitController;
use App\Http\Controllers\Api\TapToPayController;
use App\Http\Controllers\Api\TipSettingsController;
use App\Http\Controllers\Api\TaxManagementController;
use App\Http\Controllers\Api\WineManagementController;
use Illuminate\Support\Facades\Route;

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

Route::post('/twilio/waiting-list', [TwilioWebhookController::class, 'waitingList']);

Route::prefix('mobile')->group(function () {
    Route::post('/broadcasting/auth', [BroadcastController::class, 'authenticate'])->middleware('mobile.api');
    Route::post('/login', [MobileAuthController::class, 'login']);

    Route::middleware('mobile.api')->group(function () {
        Route::post('/logout', [MobileAuthController::class, 'logout']);
    });
    Route::middleware('mobile.api')->group(function () {
        Route::get('/settings/tips', [TipSettingsController::class, 'show']);
        Route::get('/settings/views', [MobileViewSettingsController::class, 'show']);
    });
    Route::middleware('mobile.api:server,manager,pos')->group(function () {
        Route::get('/tap-to-pay/config', [TapToPayController::class, 'config']);
        Route::post('/loyalty/redemptions/scan', [LoyaltyRedemptionController::class, 'scan']);
    });

    Route::prefix('servers')->middleware('mobile.api:server,manager')->group(function () {
        Route::get('/dashboard', [ServerDashboardController::class, 'summary']);
        Route::get('/visits/summary', [ServerVisitController::class, 'summary']);
        Route::get('/visits/lookup', [ServerVisitController::class, 'lookup']);
        Route::post('/visits', [ServerVisitController::class, 'store']);
        Route::get('/menu/categories', [ServerMenuController::class, 'menuCategories']);
        Route::get('/cocktails/categories', [ServerMenuController::class, 'cocktailCategories']);
        Route::get('/wines/categories', [ServerMenuController::class, 'wineCategories']);
        Route::get('/cantina/categories', [ServerMenuController::class, 'cantinaCategories']);
        Route::get('/tables', [DiningTableController::class, 'index']);
        Route::patch('/tables/{diningTable}/status', [DiningTableController::class, 'updateStatus']);
        Route::get('/servers/available', [ServerTableSessionController::class, 'availableServers']);
        Route::post('/terminal/connection-token', [StripeTerminalController::class, 'connectionToken']);
        Route::post('/terminal/payment-intents', [StripeTerminalController::class, 'createPaymentIntent']);
        Route::post('/table-sessions', [ServerTableSessionController::class, 'store']);
        Route::get('/table-sessions/active', [ServerTableSessionController::class, 'active']);
        Route::get('/table-sessions/{tableSession}', [ServerTableSessionController::class, 'show']);
        Route::get('/table-sessions/{tableSession}/orders', [ServerTableSessionController::class, 'orders']);
        Route::post('/table-sessions/{tableSession}/orders', [ServerTableSessionController::class, 'storeOrder']);
        Route::patch('/table-sessions/{tableSession}/renew', [ServerTableSessionController::class, 'renew']);
        Route::patch('/table-sessions/{tableSession}/order-mode', [ServerTableSessionController::class, 'updateOrderMode']);
        Route::patch('/table-sessions/{tableSession}/close', [ServerTableSessionController::class, 'close']);
        Route::patch('/table-sessions/{tableSession}/pay', [ServerTableSessionController::class, 'pay']);
        Route::post('/table-sessions/{tableSession}/payments', [ServerTableSessionController::class, 'addPayment']);
        Route::post('/table-sessions/{tableSession}/payments/external', [ServerTableSessionController::class, 'confirmExternalPayment']);
        Route::post('/table-sessions/{tableSession}/receipt', [ServerTableSessionController::class, 'sendReceipt']);
        Route::patch('/table-sessions/{tableSession}/transfer', [ServerTableSessionController::class, 'transfer']);
        Route::patch('/orders/{order}/confirm', [ServerOrderController::class, 'confirm']);
        Route::patch('/orders/{order}/cancel', [ServerOrderController::class, 'cancel']);
        Route::patch('/orders/{order}/items/{item}/void', [OrderItemController::class, 'void']);
        Route::patch('/orders/{order}/items/{item}/override', [OrderItemController::class, 'override']);
    });

    Route::prefix('kitchen')->middleware('mobile.api:kitchen,manager,server')->group(function () {
        Route::get('/areas', [KitchenAreaController::class, 'index']);
        Route::get('/orders', [KitchenOrderController::class, 'index']);
        Route::patch('/order-items/{orderItem}', [KitchenOrderController::class, 'update']);
    });

    Route::prefix('pos')->middleware('mobile.api:pos,manager')->group(function () {
        Route::get('/tickets', [PosTicketController::class, 'index']);
        Route::post('/tickets', [PosTicketController::class, 'store']);
        Route::get('/tickets/{tableSession}', [PosTicketController::class, 'show']);
        Route::post('/tickets/{tableSession}/batches', [PosTicketController::class, 'addItems']);
        Route::post('/tickets/{tableSession}/payments', [PosTicketController::class, 'addPayment']);
        Route::post('/tickets/{tableSession}/payments/external', [PosTicketController::class, 'confirmExternalPayment']);
        Route::post('/tickets/{tableSession}/receipt', [PosTicketController::class, 'sendReceipt']);
        Route::patch('/tickets/{tableSession}/pay', [PosTicketController::class, 'pay']);
        Route::post('/terminal/connection-token', [StripeTerminalController::class, 'connectionToken']);
        Route::post('/terminal/payment-intents', [StripeTerminalController::class, 'createPaymentIntent']);

        Route::get('/menu/categories', [ServerMenuController::class, 'menuCategories']);
        Route::get('/cocktails/categories', [ServerMenuController::class, 'cocktailCategories']);
        Route::get('/wines/categories', [ServerMenuController::class, 'wineCategories']);
        Route::get('/cantina/categories', [ServerMenuController::class, 'cantinaCategories']);
        Route::get('/tables', [DiningTableController::class, 'index']);
        Route::patch('/tables/{diningTable}/status', [DiningTableController::class, 'updateStatus']);
        Route::patch('/batches/{order}/confirm', [ServerOrderController::class, 'confirm']);
        Route::patch('/batches/{order}/cancel', [ServerOrderController::class, 'cancel']);
        Route::patch('/batches/{order}/items/{item}/void', [OrderItemController::class, 'void']);
        Route::patch('/batches/{order}/items/{item}/override', [OrderItemController::class, 'override']);
    });

    Route::prefix('hosts')->middleware('mobile.api:host,manager')->group(function () {
        Route::get('/tables', [DiningTableController::class, 'index']);
        Route::patch('/tables/{diningTable}/status', [DiningTableController::class, 'updateStatus']);
        Route::get('/waiting-list', [WaitingListController::class, 'index']);
        Route::post('/waiting-list', [WaitingListController::class, 'store']);
        Route::patch('/waiting-list/{waitingListEntry}', [WaitingListController::class, 'update']);
        Route::post('/waiting-list/{waitingListEntry}/notify', [WaitingListController::class, 'notify']);
        Route::post('/waiting-list/{waitingListEntry}/assign', [WaitingListController::class, 'assignTables']);
        Route::get('/waiting-list/settings', [WaitingListController::class, 'settings']);
        Route::patch('/waiting-list/settings', [WaitingListController::class, 'updateSettings']);
        Route::get('/servers/available', [ServerTableSessionController::class, 'availableServers']);
    });

    Route::prefix('managers')->middleware('mobile.api:manager')->group(function () {
        Route::get('/dashboard', [ManagerDashboardController::class, 'summary']);
        Route::get('/operations', [ManagerDashboardController::class, 'operations']);
        Route::get('/servers', [ManagerDashboardController::class, 'servers']);
        Route::get('/tables', [DiningTableController::class, 'index']);
        Route::post('/tables', [DiningTableController::class, 'store']);
        Route::put('/tables/{diningTable}', [DiningTableController::class, 'update']);
        Route::patch('/tables/{diningTable}/status', [DiningTableController::class, 'updateStatus']);
        Route::delete('/tables/{diningTable}', [DiningTableController::class, 'destroy']);

        Route::get('/waiting-list', [WaitingListController::class, 'index']);
        Route::post('/waiting-list', [WaitingListController::class, 'store']);
        Route::patch('/waiting-list/{waitingListEntry}', [WaitingListController::class, 'update']);
        Route::post('/waiting-list/{waitingListEntry}/notify', [WaitingListController::class, 'notify']);
        Route::post('/waiting-list/{waitingListEntry}/assign', [WaitingListController::class, 'assignTables']);
        Route::get('/waiting-list/settings', [WaitingListController::class, 'settings']);
        Route::patch('/waiting-list/settings', [WaitingListController::class, 'updateSettings']);
        Route::patch('/servers/{user}/toggle', [ManagerDashboardController::class, 'toggleServer']);
        Route::get('/payments', [PaymentManagementController::class, 'index']);
        Route::post('/payments/{payment}/refund', [PaymentManagementController::class, 'refund']);
        Route::post('/payments/{payment}/void', [PaymentManagementController::class, 'void']);
        Route::get('/prep-areas', [PrepAreaManagementController::class, 'index']);
        Route::post('/prep-areas', [PrepAreaManagementController::class, 'store']);
        Route::put('/prep-areas/{prepArea}', [PrepAreaManagementController::class, 'update']);
        Route::delete('/prep-areas/{prepArea}', [PrepAreaManagementController::class, 'destroy']);
        Route::get('/prep-labels', [PrepLabelManagementController::class, 'index']);
        Route::post('/prep-labels', [PrepLabelManagementController::class, 'store']);
        Route::put('/prep-labels/{prepLabel}', [PrepLabelManagementController::class, 'update']);
        Route::delete('/prep-labels/{prepLabel}', [PrepLabelManagementController::class, 'destroy']);

        Route::get('/menu/categories', [MenuManagementController::class, 'categories']);
        Route::post('/menu/categories', [MenuManagementController::class, 'storeCategory']);
        Route::put('/menu/categories/{category}', [MenuManagementController::class, 'updateCategory']);
        Route::delete('/menu/categories/{category}', [MenuManagementController::class, 'destroyCategory']);
        Route::post('/menu/categories/reorder', [MenuManagementController::class, 'reorderCategories']);
        Route::post('/menu/dishes', [MenuManagementController::class, 'storeDish']);
        Route::post('/menu/dishes/reorder', [MenuManagementController::class, 'reorderDishes']);
        Route::put('/menu/dishes/{dish}', [MenuManagementController::class, 'updateDish']);
        Route::delete('/menu/dishes/{dish}', [MenuManagementController::class, 'destroyDish']);
        Route::patch('/menu/dishes/{dish}/toggle', [MenuManagementController::class, 'toggleDish']);

        Route::get('/cocktails/categories', [CocktailManagementController::class, 'categories']);
        Route::post('/cocktails/categories', [CocktailManagementController::class, 'storeCategory']);
        Route::put('/cocktails/categories/{cocktailCategory}', [CocktailManagementController::class, 'updateCategory']);
        Route::delete('/cocktails/categories/{cocktailCategory}', [CocktailManagementController::class, 'destroyCategory']);
        Route::post('/cocktails/categories/reorder', [CocktailManagementController::class, 'reorderCategories']);
        Route::post('/cocktails/items', [CocktailManagementController::class, 'store']);
        Route::post('/cocktails/items/reorder', [CocktailManagementController::class, 'reorder']);
        Route::put('/cocktails/items/{cocktail}', [CocktailManagementController::class, 'update']);
        Route::delete('/cocktails/items/{cocktail}', [CocktailManagementController::class, 'destroy']);
        Route::patch('/cocktails/items/{cocktail}/toggle', [CocktailManagementController::class, 'toggle']);

        Route::get('/wines/categories', [WineManagementController::class, 'categories']);
        Route::post('/wines/categories', [WineManagementController::class, 'storeCategory']);
        Route::put('/wines/categories/{wineCategory}', [WineManagementController::class, 'updateCategory']);
        Route::delete('/wines/categories/{wineCategory}', [WineManagementController::class, 'destroyCategory']);
        Route::post('/wines/categories/reorder', [WineManagementController::class, 'reorderCategories']);
        Route::post('/wines/items', [WineManagementController::class, 'store']);
        Route::post('/wines/items/reorder', [WineManagementController::class, 'reorder']);
        Route::put('/wines/items/{wine}', [WineManagementController::class, 'update']);
        Route::delete('/wines/items/{wine}', [WineManagementController::class, 'destroy']);
        Route::patch('/wines/items/{wine}/toggle', [WineManagementController::class, 'toggle']);

        Route::get('/cantina/categories', [CantinaManagementController::class, 'categories']);
        Route::post('/cantina/categories', [CantinaManagementController::class, 'storeCategory']);
        Route::put('/cantina/categories/{category}', [CantinaManagementController::class, 'updateCategory']);
        Route::delete('/cantina/categories/{category}', [CantinaManagementController::class, 'destroyCategory']);
        Route::post('/cantina/categories/reorder', [CantinaManagementController::class, 'reorderCategories']);
        Route::post('/cantina/items', [CantinaManagementController::class, 'store']);
        Route::post('/cantina/items/reorder', [CantinaManagementController::class, 'reorder']);
        Route::put('/cantina/items/{cantinaItem}', [CantinaManagementController::class, 'update']);
        Route::delete('/cantina/items/{cantinaItem}', [CantinaManagementController::class, 'destroy']);
        Route::patch('/cantina/items/{cantinaItem}/toggle', [CantinaManagementController::class, 'toggle']);

        Route::get('/campaigns', [CampaignController::class, 'index']);
        Route::post('/campaigns', [CampaignController::class, 'store']);
        Route::put('/campaigns/{popup}', [CampaignController::class, 'update']);
        Route::delete('/campaigns/{popup}', [CampaignController::class, 'destroy']);
        Route::patch('/campaigns/{popup}/toggle', [CampaignController::class, 'toggle']);

        Route::get('/extras', [ExtraManagementController::class, 'index']);
        Route::post('/extras', [ExtraManagementController::class, 'store']);
        Route::put('/extras/{extra}', [ExtraManagementController::class, 'update']);
        Route::delete('/extras/{extra}', [ExtraManagementController::class, 'destroy']);

        Route::get('/taxes', [TaxManagementController::class, 'index']);
        Route::post('/taxes', [TaxManagementController::class, 'store']);
        Route::put('/taxes/{tax}', [TaxManagementController::class, 'update']);
        Route::delete('/taxes/{tax}', [TaxManagementController::class, 'destroy']);
    });
});

// Alias para compatibilidad con builds anteriores del app.
Route::prefix('mobileserver')->middleware('mobile.api:server,manager')->group(function () {
    Route::post('/table-session', [ServerTableSessionController::class, 'store']);
    Route::get('/table-session/active', [ServerTableSessionController::class, 'active']);
    Route::get('/table-session/{tableSession}', [ServerTableSessionController::class, 'show']);
    Route::get('/table-session/{tableSession}/orders', [ServerTableSessionController::class, 'orders']);
    Route::post('/table-session/{tableSession}/orders', [ServerTableSessionController::class, 'storeOrder']);
    Route::patch('/table-session/{tableSession}/renew', [ServerTableSessionController::class, 'renew']);
    Route::patch('/table-session/{tableSession}/close', [ServerTableSessionController::class, 'close']);
    Route::patch('/orders/{order}/confirm', [ServerOrderController::class, 'confirm']);
    Route::patch('/orders/{order}/cancel', [ServerOrderController::class, 'cancel']);
});

// Rutas legacy para compatibilidad con versiones anteriores de la app.
Route::prefix('servers')->group(function () {
    Route::post('/login', [MobileAuthController::class, 'login']);

    Route::middleware('mobile.api:server,manager')->group(function () {
        Route::post('/logout', [MobileAuthController::class, 'logout']);
        Route::get('/visits/summary', [ServerVisitController::class, 'summary']);
        Route::post('/visits', [ServerVisitController::class, 'store']);
    });

    Route::middleware('mobile.api:server,manager,pos')->group(function () {
        Route::post('/loyalty/redemptions/scan', [LoyaltyRedemptionController::class, 'scan']);
    });
});
