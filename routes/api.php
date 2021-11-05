<?php

use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientDeliveryNoteController;
use App\Http\Controllers\CompartmentController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\GoodToRemoveController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\JuridicPersonalityController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProviderTypeController;
use App\Http\Controllers\PurchaseCouponController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalePointController;
use App\Http\Controllers\StockTypeController;
use App\Http\Controllers\TankController;
use App\Http\Controllers\TournController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TransferDemandController;
use App\Http\Controllers\TruckController;
use App\Http\Controllers\UnityController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::post('/login', [AuthUserController::class, 'login']);
Route::post('/register', [AuthUserController::class, 'register']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/product/{$name}/search', [ProductController::class, 'search']);

// Category routes
Route::get('/category', [CategoryController::class, 'index']);
Route::post('/category', [CategoryController::class, 'store']);
Route::patch('/category/{id}/update', [CategoryController::class, 'update']);
Route::delete('/category/{id}/destroy', [CategoryController::class, 'destroy']);
Route::get('/category/{id}/show', [CategoryController::class, 'show']);
Route::get('/category/{id}/sub-categories', [CategoryController::class, 'subCategoriesOfCategory']);

// Sub category routes
Route::get('/sub-category', [SubCategoryController::class, 'index']);
Route::post('/sub-category', [SubCategoryController::class, 'store']);
Route::patch('/sub-category/{id}/update', [SubCategoryController::class, 'update']);
Route::delete('/sub-category/{id}/destroy', [SubCategoryController::class, 'destroy']);
Route::get('/sub-category/{id}/show', [SubCategoryController::class, 'show']);

// Unity routes
Route::get('/unity', [UnityController::class, 'index']);
Route::post('/unity', [UnityController::class, 'store']);
Route::patch('/unity/{id}/update', [UnityController::class, 'update']);
Route::delete('/unity/{id}/destroy', [UnityController::class, 'destroy']);
Route::get('/unity/{id}/show', [UnityController::class, 'show']);

// Stock type routes
Route::get('/stock-type', [StockTypeController::class, 'index']);
Route::post('/stock-type', [StockTypeController::class, 'store']);
Route::patch('/stock-type/{id}/update', [StockTypeController::class, 'update']);
Route::delete('/stock-type/{id}/destroy', [StockTypeController::class, 'destroy']);
Route::get('/stock-type/{id}/show', [StockTypeController::class, 'show']);

// Client routes
Route::get('/client', [ClientController::class, 'index']);
Route::post('/client', [ClientController::class, 'store']);
Route::get('/client/{id}/show', [ClientController::class, 'show']);
Route::patch('/client/{id}/update', [ClientController::class, 'update']);
Route::delete('/client/{id}/destroy', [ClientController::class, 'destroy']);
Route::get('/code', [ClientController::class, 'returnCode']);

// Provider routes
Route::get('/provider', [ProviderController::class, 'index']);
Route::post('/provider', [ProviderController::class, 'store']);
Route::get('/provider/{id}/show', [ProviderController::class, 'show']);
Route::get('/provider/{id}/update', [ProviderController::class, 'edit']);
Route::patch('/provider/{id}/update', [ProviderController::class, 'update']);
Route::delete('/provider/{id}/destroy', [ProviderController::class, 'destroy']);

// Provider type routes
Route::get('/provider-type', [ProviderTypeController::class, 'index']);
Route::post('/provider-type', [ProviderTypeController::class, 'store']);
Route::get('/provider-type/{id}/show', [ProviderTypeController::class, 'show']);
Route::get('/provider-type/{id}/update', [ProviderTypeController::class, 'edit']);
Route::patch('/provider-type/{id}/update', [ProviderTypeController::class, 'update']);
Route::delete('/provider-type/{id}/destroy', [ProviderTypeController::class, 'destroy']);

// Product routes
Route::get('/product', [ProductController::class, 'index']);
Route::post('/product', [ProductController::class, 'store']);
Route::get('/product/{id}/show', [ProductController::class, 'show']);
Route::patch('/product/{id}/update', [ProductController::class, 'update']);
Route::delete('/product/{id}/destroy', [ProductController::class, 'destroy']);

// Purchase order routes
Route::get('/purchase-order', [PurchaseOrderController::class, 'index']);
Route::post('/purchase-order', [PurchaseOrderController::class, 'store']);
Route::get('/purchase-order/{id}/show', [PurchaseOrderController::class, 'show']);
Route::patch('/purchase-order/{id}/update', [PurchaseOrderController::class, 'update']);
Route::delete('/purchase-order/{id}/destroy', [PurchaseOrderController::class, 'destroy']);

// Purchase coupon routes
Route::get('/purchase-coupon', [PurchaseCouponController::class, 'index']);
Route::post('/purchase-coupon', [PurchaseCouponController::class, 'store']);
Route::get('/purchase-coupon-from-purchase-order', [PurchaseCouponController::class, 'indexFromPurchaseOrder']);
Route::post('/purchase-coupon-from-purchase-order', [PurchaseCouponController::class, 'storeFromPurchaseOrder']);
Route::get('/purchase-coupon/{id}/show', [PurchaseCouponController::class, 'show']);
Route::get('/purchase-coupon/{id}/update', [PurchaseCouponController::class, 'edit']);
Route::patch('/purchase-coupon/{id}/update', [PurchaseCouponController::class, 'update']);
Route::get('/purchase-coupon-from-purchase-order/{id}/update', [PurchaseCouponController::class, 'editFromPurchaseOrder']);
Route::patch('/purchase-coupon-from-purchase-order/{id}/update', [PurchaseCouponController::class, 'updateFromPurchaseOrder']);
Route::delete('/purchase-coupon/{id}/destroy', [PurchaseCouponController::class, 'destroy']);

// Delivery note routes
Route::get('/delivery-note', [DeliveryNoteController::class, 'index']);
Route::post('/delivery-note', [DeliveryNoteController::class, 'store']);
Route::get('/delivery-note/{id}/show', [DeliveryNoteController::class, 'show']);
Route::patch('/delivery-note/{id}/update', [DeliveryNoteController::class, 'update']);
Route::delete('/delivery-note/{id}/destroy', [DeliveryNoteController::class, 'destroy']);

// Order routes
Route::get('/order', [OrderController::class, 'index']);
Route::post('/order', [OrderController::class, 'store']);
Route::get('/order/{id}/show', [OrderController::class, 'show']);
Route::patch('/order/{id}/update', [OrderController::class, 'update']);
Route::delete('/order/{id}/destroy', [OrderController::class, 'destroy']);

// Sale routes
Route::get('/sale', [SaleController::class, 'index']);
Route::post('/sale', [SaleController::class, 'store']);
Route::get('/sale-from-order', [SaleController::class, 'indexFromOrder']);
Route::post('/sale-from-order', [SaleController::class, 'storeFromOrder']);
Route::get('/sale/{id}/show', [SaleController::class, 'show']);
Route::get('/sale/{id}/update', [SaleController::class, 'edit']);
Route::patch('/sale/{id}/update', [SaleController::class, 'update']);
Route::get('/sale-from-order/{id}/update', [SaleController::class, 'editFromOrder']);
Route::patch('/sale-from-order/{id}/update', [SaleController::class, 'updateFromOrder']);
Route::delete('/sale/{id}/destroy', [SaleController::class, 'destroy']);

// Client delivery note routes
Route::get('/client-delivery-note', [ClientDeliveryNoteController::class, 'index']);
Route::post('/client-delivery-note', [ClientDeliveryNoteController::class, 'store']);
Route::get('/client-delivery-note/{id}/show', [ClientDeliveryNoteController::class, 'show']);
Route::patch('/client-delivery-note/{id}/update', [ClientDeliveryNoteController::class, 'update']);
Route::delete('/client-delivery-note/{id}/destroy', [ClientDeliveryNoteController::class, 'destroy']);

// Institution routes
Route::get('/institution', [InstitutionController::class, 'index']);
Route::post('/institution', [InstitutionController::class, 'store']);
Route::get('/institution/{id}/show', [InstitutionController::class, 'show']);
Route::patch('/institution/{id}/update', [InstitutionController::class, 'update']);
Route::delete('/institution/{id}/destroy', [InstitutionController::class, 'destroy']);

// Sale point routes
Route::get('/sale-point', [SalePointController::class, 'index']);
Route::post('/sale-point', [SalePointController::class, 'store']);
Route::get('/sale-point/{id}/show', [SalePointController::class, 'show']);
Route::patch('/sale-point/{id}/update', [SalePointController::class, 'update']);
Route::delete('/sale-point/{id}/destroy', [SalePointController::class, 'destroy']);

// Transfer demand routes
Route::get('/transfer-demand', [TransferDemandController::class, 'index']);
Route::post('/transfer-demand', [TransferDemandController::class, 'store']);
Route::get('/transfer-demand/{id}/show', [TransferDemandController::class, 'show']);
Route::patch('/transfer-demand/{id}/update', [TransferDemandController::class, 'update']);
Route::delete('/transfer-demand/{id}/destroy', [TransferDemandController::class, 'destroy']);
Route::patch('/transfer-demand/{id}/validate', [TransferDemandController::class, 'validateTransferDemand']);
Route::patch('/transfer-demand/{id}/cancel', [TransferDemandController::class, 'cancelTransferDemand']);

// Transfer routes
Route::get('/transfer', [TransferController::class, 'index']);
Route::post('/transfer', [TransferController::class, 'store']);
Route::get('/transfer/{id}/show', [TransferController::class, 'show']);
Route::patch('/transfer/{id}/update', [TransferController::class, 'update']);
Route::delete('/transfer/{id}/destroy', [TransferController::class, 'destroy']);

// Compartment routes
Route::get('/compartment', [CompartmentController::class, 'index']);
Route::post('/compartment', [CompartmentController::class, 'store']);
Route::get('/compartment/{id}/show', [CompartmentController::class, 'show']);
Route::patch('/compartment/{id}/update', [CompartmentController::class, 'update']);
Route::delete('/compartment/{id}/destroy', [CompartmentController::class, 'destroy']);

// Tank routes
Route::get('/tank', [TankController::class, 'index']);
Route::post('/tank', [TankController::class, 'store']);
Route::get('/tank/{id}/show', [TankController::class, 'show']);
Route::patch('/tank/{id}/update', [TankController::class, 'update']);
Route::delete('/tank/{id}/destroy', [TankController::class, 'destroy']);

// Truck routes
Route::get('/truck', [TruckController::class, 'index']);
Route::post('/truck', [TruckController::class, 'store']);
Route::get('/truck/{id}/show', [TruckController::class, 'show']);
Route::patch('/truck/{id}/update', [TruckController::class, 'update']);
Route::delete('/truck/{id}/destroy', [TruckController::class, 'destroy']);

// Truck routes
Route::get('/tourn', [TournController::class, 'index']);
Route::post('/tourn', [TournController::class, 'store']);
Route::get('/tourn/{id}/show', [TournController::class, 'show']);
Route::patch('/tourn/{id}/update', [TournController::class, 'update']);
Route::delete('/tourn/{id}/destroy', [TournController::class, 'destroy']);

// Truck routes
Route::get('/destination', [DestinationController::class, 'index']);
Route::post('/destination', [DestinationController::class, 'store']);
Route::get('/destination/{id}/show', [DestinationController::class, 'show']);
Route::patch('/destination/{id}/update', [DestinationController::class, 'update']);
Route::delete('/destination/{id}/destroy', [DestinationController::class, 'destroy']);

// Good to remove routes
Route::get('/good-to-remove', [GoodToRemoveController::class, 'index']);
Route::post('/good-to-remove', [GoodToRemoveController::class, 'store']);
Route::get('/good-to-remove/{id}/show', [GoodToRemoveController::class, 'show']);
Route::patch('/good-to-remove/{id}/update', [GoodToRemoveController::class, 'update']);
Route::delete('/good-to-remove/{id}/destroy', [GoodToRemoveController::class, 'destroy']);




// Protected routes
Route::group(
    [
        'middleware' => 'api',
        'namespace' => 'App\Http\Controllers',
        'prefix' => 'auth'
    ],
    function ($router) {
        Route::post('/logout', [AuthUserController::class, 'logout']);
    }
);

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
