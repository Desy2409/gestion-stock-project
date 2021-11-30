<?php

use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientDeliveryNoteController;
use App\Http\Controllers\CompartmentController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\DeliveryPointController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\EmailChannelParamController;
use App\Http\Controllers\EmployeeFunctionController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\FileTypeController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\GoodToRemoveController;
use App\Http\Controllers\HostController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\JuridicPersonalityController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageOperationController;
use App\Http\Controllers\PhoneOperatorController;
use App\Http\Controllers\ProviderTypeController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalePointController;
use App\Http\Controllers\StockTypeController;
use App\Http\Controllers\TankController;
use App\Http\Controllers\TankTruckController;
use App\Http\Controllers\TournController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TransferDemandController;
use App\Http\Controllers\TruckController;
use App\Http\Controllers\UserTypeController;
use App\Http\Controllers\UnityController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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




// Protected routes

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthUserController::class, 'logout']);
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
    Route::get('/client-code', [ClientController::class, 'showNextCode']);
    Route::post('/client', [ClientController::class, 'store']);
    Route::get('/client/{id}/show', [ClientController::class, 'show']);
    Route::patch('/client/{id}/update', [ClientController::class, 'update']);
    Route::delete('/client/{id}/destroy', [ClientController::class, 'destroy']);
    Route::get('/code', [ClientController::class, 'returnCode']);

    // Provider routes
    Route::get('/provider', [ProviderController::class, 'index']);
    Route::get('/provider-code', [ProviderController::class, 'showNextCode']);
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
    Route::get('/product-code', [ProductController::class, 'showNextCode']);
    Route::post('/product', [ProductController::class, 'store']);
    Route::get('/product/{id}/show', [ProductController::class, 'show']);
    Route::patch('/product/{id}/update', [ProductController::class, 'update']);
    Route::delete('/product/{id}/destroy', [ProductController::class, 'destroy']);

    // Order routes
    Route::get('/order', [OrderController::class, 'index']);
    Route::get('/order-code', [OrderController::class, 'showNextCode']);
    Route::post('/order', [OrderController::class, 'store']);
    Route::get('/order/{id}/show', [OrderController::class, 'show']);
    Route::patch('/order/{id}/update', [OrderController::class, 'update']);
    Route::delete('/order/{id}/destroy', [OrderController::class, 'destroy']);
    Route::patch('/order/{id}/validate', [OrderController::class, 'validateOrder']);
    Route::patch('/order/{id}/reject', [OrderController::class, 'rejectOrder']);

    // Purchase routes
    Route::get('/purchase-on-order', [PurchaseController::class, 'purchaseOnOrder']);
    Route::get('/purchase-on-order-datas/{id}', [PurchaseController::class, 'datasFromOrder']);
    Route::get('/purchase-direct', [PurchaseController::class, 'directPurchase']);
    Route::get('/purchase-code', [PurchaseController::class, 'showNextCode']);
    Route::post('/purchase', [PurchaseController::class, 'store']);
    Route::get('/purchase/{id}/show', [PurchaseController::class, 'show']);
    Route::patch('/purchase/{id}/update', [PurchaseController::class, 'update']);
    Route::delete('/purchase/{id}/destroy', [PurchaseController::class, 'destroy']);
    Route::patch('/purchase/{id}/validate', [PurchaseController::class, 'validatePurchase']);
    Route::patch('/purchase/{id}/reject', [PurchaseController::class, 'rejectPurchase']);

    // Delivery note routes
    Route::get('/delivery-note', [DeliveryNoteController::class, 'index']);
    Route::get('/delivery-note-code', [DeliveryNoteController::class, 'showNextCode']);
    Route::get('/delivery-note-order-select/{id}', [DeliveryNoteController::class, 'datasOnSelectOrder']);
    Route::post('/delivery-note', [DeliveryNoteController::class, 'store']);
    Route::get('/delivery-note/{id}/show', [DeliveryNoteController::class, 'show']);
    Route::patch('/delivery-note/{id}/update', [DeliveryNoteController::class, 'update']);
    Route::delete('/delivery-note/{id}/destroy', [DeliveryNoteController::class, 'destroy']);
    Route::patch('/delivery-note/{id}/validate', [DeliveryNoteController::class, 'validateDeliveryNote']);
    Route::patch('/delivery-note/{id}/reject', [DeliveryNoteController::class, 'rejectDeliveryNote']);

    // Purchase order routes
    Route::get('/purchase-order', [PurchaseOrderController::class, 'index']);
    Route::get('/purchase-order-code', [PurchaseOrderController::class, 'showNextCode']);
    Route::post('/purchase-order', [PurchaseOrderController::class, 'store']);
    Route::get('/purchase-order/{id}/show', [PurchaseOrderController::class, 'show']);
    Route::patch('/purchase-order/{id}/update', [PurchaseOrderController::class, 'update']);
    Route::delete('/purchase-order/{id}/destroy', [PurchaseOrderController::class, 'destroy']);
    Route::patch('/purchase-order/{id}/validate', [PurchaseOrderController::class, 'validatePurchaseOrder']);
    Route::patch('/purchase-order/{id}/reject', [PurchaseOrderController::class, 'rejectPurchaseOrder']);

    // Sale routes
    Route::get('/sale-on-purchase-order', [SaleController::class, 'saleOnPurchaseOrder']);
    Route::get('/sale-on-purchase-order/{id}', [SaleController::class, 'datasFromPurchaseOrder']);
    Route::get('/sale-direct', [SaleController::class, 'directSale']);
    Route::get('/sale-code', [SaleController::class, 'showNextCode']);
    Route::post('/sale', [SaleController::class, 'store']);
    Route::get('/sale/{id}/show', [SaleController::class, 'show']);
    Route::patch('/sale/{id}/update', [SaleController::class, 'update']);
    Route::delete('/sale/{id}/destroy', [SaleController::class, 'destroy']);
    Route::patch('/sale/{id}/validate', [SaleController::class, 'validateSale']);
    Route::patch('/sale/{id}/reject', [SaleController::class, 'rejectSale']);

    // Client delivery note routes
    Route::get('/client-delivery-note', [ClientDeliveryNoteController::class, 'index']);
    Route::get('/client-delivery-note-code', [ClientDeliveryNoteController::class, 'showNextCode']);
    Route::get('/client-delivery-note-purchase-order-select/{id}', [ClientDeliveryNoteController::class, 'datasOnSelectPurchaseOrder']);
    Route::post('/client-delivery-note', [ClientDeliveryNoteController::class, 'store']);
    Route::get('/client-delivery-note/{id}/show', [ClientDeliveryNoteController::class, 'show']);
    Route::patch('/client-delivery-note/{id}/update', [ClientDeliveryNoteController::class, 'update']);
    Route::delete('/client-delivery-note/{id}/destroy', [ClientDeliveryNoteController::class, 'destroy']);
    Route::patch('/client-delivery-note/{id}/validate', [ClientDeliveryNoteController::class, 'validateDeliveryNote']);
    Route::patch('/client-delivery-note/{id}/reject', [ClientDeliveryNoteController::class, 'rejectDeliveryNote']);

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
    Route::get('/transfer-demand-code', [TransferDemandController::class, 'showNextCode']);
    Route::post('/transfer-demand', [TransferDemandController::class, 'store']);
    Route::get('/transfer-demand/{id}/show', [TransferDemandController::class, 'show']);
    Route::patch('/transfer-demand/{id}/update', [TransferDemandController::class, 'update']);
    Route::delete('/transfer-demand/{id}/destroy', [TransferDemandController::class, 'destroy']);
    Route::patch('/transfer-demand/{id}/validate', [TransferDemandController::class, 'validateTransferDemand']);
    Route::patch('/transfer-demand/{id}/reject', [TransferDemandController::class, 'rejectTransferDemand']);
    Route::patch('/transfer-demand/{id}/transform-to-transfer', [TransferDemandController::class, 'transformDemandToTransfer']);

    // Transfer routes
    Route::get('/transfer', [TransferController::class, 'index']);
    Route::get('/transfer-code', [TransferController::class, 'showNextCode']);
    Route::get('/transfer-transfer-demand-select/{id}', [TransferController::class, 'datasOnSelectTransferDemand']);
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

    // Tank truck routes
    Route::get('/tank-truck', [TankTruckController::class, 'index']);
    Route::post('/tank-truck', [TankTruckController::class, 'store']);
    Route::get('/tank-truck/{id}/show', [TankTruckController::class, 'show']);
    Route::patch('/tank-truck/{id}/update', [TankTruckController::class, 'update']);
    Route::delete('/tank-truck/{id}/destroy', [TankTruckController::class, 'destroy']);

    // Tourn routes
    Route::get('/tourn', [TournController::class, 'index']);
    Route::get('/tourn-code', [TournController::class, 'showNextCode']);
    Route::post('/tourn', [TournController::class, 'store']);
    Route::get('/tourn/{id}/show', [TournController::class, 'show']);
    Route::patch('/tourn/{id}/update', [TournController::class, 'update']);
    Route::delete('/tourn/{id}/destroy', [TournController::class, 'destroy']);

    // Destination routes
    Route::get('/destination', [DestinationController::class, 'index']);
    Route::post('/destination', [DestinationController::class, 'store']);
    Route::get('/destination/{id}/show', [DestinationController::class, 'show']);
    Route::patch('/destination/{id}/update', [DestinationController::class, 'update']);
    Route::delete('/destination/{id}/destroy', [DestinationController::class, 'destroy']);

    // Good to remove routes
    Route::get('/good-to-remove', [GoodToRemoveController::class, 'index']);
    Route::get('/good-to-remove-code', [GoodToRemoveController::class, 'showNextCode']);
    Route::post('/good-to-remove', [GoodToRemoveController::class, 'store']);
    Route::get('/good-to-remove/{id}/show', [GoodToRemoveController::class, 'show']);
    Route::patch('/good-to-remove/{id}/update', [GoodToRemoveController::class, 'update']);
    Route::delete('/good-to-remove/{id}/destroy', [GoodToRemoveController::class, 'destroy']);

    // Extension routes
    Route::get('/extension', [ExtensionController::class, 'index']);
    Route::post('/extension', [ExtensionController::class, 'store']);
    Route::get('/extension/{id}/show', [ExtensionController::class, 'show']);
    Route::patch('/extension/{id}/update', [ExtensionController::class, 'update']);
    Route::delete('/extension/{id}/destroy', [ExtensionController::class, 'destroy']);

    // FileType routes
    Route::get('/file-type', [FileTypeController::class, 'index']);
    Route::post('/file-type', [FileTypeController::class, 'store']);
    Route::get('/file-type/{id}/show', [FileTypeController::class, 'show']);
    Route::patch('/file-type/{id}/update', [FileTypeController::class, 'update']);
    Route::delete('/file-type/{id}/destroy', [FileTypeController::class, 'destroy']);

    // Folder routes
    Route::get('/folder', [FolderController::class, 'index']);
    Route::post('/folder', [FolderController::class, 'store']);
    Route::get('/folder/{id}/show', [FolderController::class, 'show']);
    Route::patch('/folder/{id}/update', [FolderController::class, 'update']);
    Route::delete('/folder/{id}/destroy', [FolderController::class, 'destroy']);

    // PhoneOperator routes
    Route::get('/phone-operator', [PhoneOperatorController::class, 'index']);
    Route::post('/phone-operator', [PhoneOperatorController::class, 'store']);
    Route::get('/phone-operator/{id}/show', [PhoneOperatorController::class, 'show']);
    Route::patch('/phone-operator/{id}/update', [PhoneOperatorController::class, 'update']);
    Route::delete('/phone-operator/{id}/destroy', [PhoneOperatorController::class, 'destroy']);

    // DeliveryPoint routes
    Route::get('/delivery-point', [DeliveryPointController::class, 'index']);
    Route::post('/delivery-point', [DeliveryPointController::class, 'store']);
    Route::get('/delivery-point/{id}/show', [DeliveryPointController::class, 'show']);
    Route::patch('/delivery-point/{id}/update', [DeliveryPointController::class, 'update']);
    Route::delete('/delivery-point/{id}/destroy', [DeliveryPointController::class, 'destroy']);

    // Driver routes
    Route::get('/driver', [DriverController::class, 'index']);
    Route::get('/driver/{id}/hosts', [DriverController::class, 'hostsOfDriver']);
    Route::post('/driver', [DriverController::class, 'store']);
    Route::get('/driver/{id}/show', [DriverController::class, 'show']);
    Route::patch('/driver/{id}/update', [DriverController::class, 'update']);
    Route::delete('/driver/{id}/destroy', [DriverController::class, 'destroy']);

    // Host routes
    Route::get('/host', [HostController::class, 'index']);
    Route::post('/host', [HostController::class, 'store']);
    Route::get('/host/{id}/show', [HostController::class, 'show']);
    Route::patch('/host/{id}/update', [HostController::class, 'update']);
    Route::delete('/host/{id}/destroy', [HostController::class, 'destroy']);

    // Email channel param routes
    Route::get('/email-channel-param', [EmailChannelParamController::class, 'index']);
    Route::post('/email-channel-param', [EmailChannelParamController::class, 'store']);
    Route::get('/email-channel-param/{id}/show', [EmailChannelParamController::class, 'show']);
    Route::patch('/email-channel-param/{id}/update', [EmailChannelParamController::class, 'update']);
    Route::delete('/email-channel-param/{id}/destroy', [EmailChannelParamController::class, 'destroy']);

    Route::prefix('user')->group(function () {
        // Operation routes
        Route::get('/operation', [OperationController::class, 'index']);
        // Route::post('/operation', [OperationController::class, 'store']);
        // Route::get('/operation/{id}/show', [OperationController::class, 'show']);
        Route::get('/operation/{id}/roles', [OperationController::class, 'rolesOfOperation']);
        // Route::patch('/operation/{id}/update', [OperationController::class, 'update']);
        // Route::delete('/operation/{id}/destroy', [OperationController::class, 'destroy']);

        // Page operation routes
        Route::get('/page-operation', [PageOperationController::class, 'index']);
        // Route::post('/page-operation', [PageOperationController::class, 'store']);
        // Route::get('/page-operation/{id}/show', [PageOperationController::class, 'show']);
        Route::get('/page-operation/{id}/roles', [PageOperationController::class, 'rolesOfPageOperation']);
        // Route::patch('/page-operation/{id}/update', [PageOperationController::class, 'update']);
        // Route::delete('/page-operation/{id}/destroy', [PageOperationController::class, 'destroy']);

        // User type routes
        Route::get('/user-type', [UserTypeController::class, 'index']);
        Route::post('/user-type', [UserTypeController::class, 'store']);
        Route::get('/user-type/{id}/show', [UserTypeController::class, 'show']);
        Route::patch('/user-type/{id}/update', [UserTypeController::class, 'update']);
        Route::delete('/user-type/{id}/destroy', [UserTypeController::class, 'destroy']);

        // Employee function routes
        // Route::get('/employee-function', [EmployeeFunctionController::class, 'index']);
        // Route::post('/employee-function', [EmployeeFunctionController::class, 'store']);
        // Route::get('/employee-function/{id}/show', [EmployeeFunctionController::class, 'show']);
        // Route::patch('/employee-function/{id}/update', [EmployeeFunctionController::class, 'update']);
        // Route::delete('/employee-function/{id}/destroy', [EmployeeFunctionController::class, 'destroy']);

        // Role routes
        Route::get('/role', [RoleController::class, 'index']);
        Route::post('/role', [RoleController::class, 'store']);
        Route::get('/role/{id}/show', [RoleController::class, 'show']);
        Route::patch('/role/{id}/update', [RoleController::class, 'update']);
        Route::delete('/role/{id}/destroy', [RoleController::class, 'destroy']);

        // User routes
        Route::get('', [RoleController::class, 'index']);
        Route::post('', [RoleController::class, 'store']);
        Route::get('{id}/show', [RoleController::class, 'show']);
        Route::patch('{id}/update', [RoleController::class, 'update']);
        Route::delete('{id}/destroy', [RoleController::class, 'destroy']);
    });

});
