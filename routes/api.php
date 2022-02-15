<?php

use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientDeliveryNoteController;
use App\Http\Controllers\CompartmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\DeliveryPointController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\EmailChannelParamController;
use App\Http\Controllers\EmployeeFunctionController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\FileTypeController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\RemovalOrderController;
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
use App\Http\Controllers\SmsChannelParamController;
use App\Http\Controllers\StockTypeController;
use App\Http\Controllers\TankController;
use App\Http\Controllers\TankTruckController;
use App\Http\Controllers\TaxeController;
use App\Http\Controllers\TournController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TransferDemandController;
use App\Http\Controllers\TransferDemandProcessingController;
use App\Http\Controllers\TruckController;
use App\Http\Controllers\UserTypeController;
use App\Http\Controllers\UnityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
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
    // Route::get('/products', [ProductController::class, 'index']);
    // Route::get('/product/{$name}/search', [ProductController::class, 'search']);

    // Dashboard routes
    Route::get('/count', [DashboardController::class, 'count']);
    Route::get('/dash-purchase/{id}/{startDate}/{endDate}', [DashboardController::class, 'purchaseTotalAmountOfSalePoint']);
    Route::post('/dashboard-sale-point', [DashboardController::class, 'salePoints']);
    Route::post('/dashboard-graphics', [DashboardController::class, 'graphicsValues']);

    // Category routes
    Route::get('/category', [CategoryController::class, 'index']);
    Route::post('/category', [CategoryController::class, 'store']);
    Route::patch('/category/{id}/update', [CategoryController::class, 'update']);
    Route::delete('/category/{id}/destroy', [CategoryController::class, 'destroy']);
    Route::get('/category/{id}/show', [CategoryController::class, 'show']);
    Route::get('/category/{id}/sub-categories', [CategoryController::class, 'subCategoriesOfCategory']);
    Route::get('/category-report', [CategoryController::class, 'categoryReports']);

    // Sub category routes
    Route::get('/sub-category', [SubCategoryController::class, 'index']);
    Route::post('/sub-category', [SubCategoryController::class, 'store']);
    Route::patch('/sub-category/{id}/update', [SubCategoryController::class, 'update']);
    Route::delete('/sub-category/{id}/destroy', [SubCategoryController::class, 'destroy']);
    Route::get('/sub-category/{id}/show', [SubCategoryController::class, 'show']);
    Route::get('/sub-category-report', [SubCategoryController::class, 'subCategoryReports']);

    // Unity routes
    Route::get('/unity', [UnityController::class, 'index']);
    Route::post('/unity', [UnityController::class, 'store']);
    Route::patch('/unity/{id}/update', [UnityController::class, 'update']);
    Route::delete('/unity/{id}/destroy', [UnityController::class, 'destroy']);
    Route::get('/unity/{id}/show', [UnityController::class, 'show']);
    Route::get('/unity-report', [UnityController::class, 'unityReports']);

    // Stock type routes
    Route::get('/stock-type', [StockTypeController::class, 'index']);
    Route::post('/stock-type', [StockTypeController::class, 'store']);
    Route::patch('/stock-type/{id}/update', [StockTypeController::class, 'update']);
    Route::delete('/stock-type/{id}/destroy', [StockTypeController::class, 'destroy']);
    Route::get('/stock-type/{id}/show', [StockTypeController::class, 'show']);
    Route::get('/stock-type-report', [StockTypeController::class, 'stockTypeReports']);

    // Client routes
    Route::get('/client', [ClientController::class, 'index']);
    Route::get('/client-code', [ClientController::class, 'showNextCode']);
    Route::post('/client', [ClientController::class, 'store']);
    Route::get('/client/{id}/show', [ClientController::class, 'show']);
    Route::get('/client/{id}/edit', [ClientController::class, 'edit']);
    Route::patch('/client/{id}/update', [ClientController::class, 'update']);
    Route::delete('/client/{id}/destroy', [ClientController::class, 'destroy']);
    Route::get('/code', [ClientController::class, 'returnCode']);
    Route::get('/client-report', [ClientController::class, 'clientReports']);

    // Provider routes
    Route::get('/provider/{type}', [ProviderController::class, 'index']);
    Route::get('/provider-code', [ProviderController::class, 'showNextCode']);
    Route::get('/provider/on-type-select/{type}', [ProviderController::class, 'onProviderTypeSelect']);
    Route::post('/provider', [ProviderController::class, 'store']);
    Route::get('/provider/{id}/show', [ProviderController::class, 'show']);
    Route::get('/provider/{id}/edit', [ProviderController::class, 'edit']);
    Route::patch('/provider/{id}/update', [ProviderController::class, 'update']);
    Route::delete('/provider/{id}/destroy', [ProviderController::class, 'destroy']);

    // Provider type routes
    Route::get('/provider-type/{type}', [ProviderTypeController::class, 'index']);
    Route::post('/provider-type', [ProviderTypeController::class, 'store']);
    Route::get('/provider-type/{id}/show', [ProviderTypeController::class, 'show']);
    Route::get('/provider-type/{id}/edit', [ProviderTypeController::class, 'edit']);
    Route::patch('/provider-type/{id}/update', [ProviderTypeController::class, 'update']);
    Route::delete('/provider-type/{id}/destroy', [ProviderTypeController::class, 'destroy']);
    Route::get('/provider-type-report', [ProviderTypeController::class, 'providerTypeReports']);

    // Product routes
    Route::get('/product', [ProductController::class, 'index']);
    Route::get('/product-code', [ProductController::class, 'showNextCode']);
    Route::post('/product', [ProductController::class, 'store']);
    Route::get('/product/{id}/show', [ProductController::class, 'show']);
    Route::get('/product/{id}/edit', [ProductController::class, 'edit']);
    Route::patch('/product/{id}/update', [ProductController::class, 'update']);
    Route::delete('/product/{id}/destroy', [ProductController::class, 'destroy']);
    Route::delete('/product/{id}/pricing', [ProductController::class, 'pricing']);
    Route::get('/product-report', [ProductController::class, 'productReports']);

    // Order routes
    Route::get('/order', [OrderController::class, 'index']);
    Route::get('/order-code', [OrderController::class, 'showNextCode']);
    Route::get('/order-products-of-category/{id}', [OrderController::class, 'productsOfSelectedCategory']);
    Route::post('/order', [OrderController::class, 'store']);
    Route::get('/order/{id}/show', [OrderController::class, 'show']);
    Route::patch('/order/{id}/update', [OrderController::class, 'update']);
    Route::delete('/order/{id}/destroy', [OrderController::class, 'destroy']);
    Route::get('/order/{id}/edit', [OrderController::class, 'edit']);
    Route::patch('/order/{id}/{action}', [OrderController::class, 'orderProcessing']);
    Route::get('/order-report', [OrderController::class, 'orderReports']);

    // Purchase routes
    Route::get('/purchase-on-order', [PurchaseController::class, 'purchaseOnOrder']);
    Route::get('/purchase-on-order-datas/{id}', [PurchaseController::class, 'datasFromOrder']);
    Route::get('/purchase-direct', [PurchaseController::class, 'directPurchase']);
    Route::get('/purchase-code', [PurchaseController::class, 'showNextCode']);
    Route::get('/purchase-products-of-category/{id}', [PurchaseController::class, 'productsOfSelectedCategory']);
    Route::post('/purchase', [PurchaseController::class, 'store']);
    Route::get('/purchase/{id}/show', [PurchaseController::class, 'show']);
    Route::get('/purchase/{id}/edit', [PurchaseController::class, 'edit']);
    Route::patch('/purchase/{id}/update', [PurchaseController::class, 'update']);
    Route::delete('/purchase/{id}/destroy', [PurchaseController::class, 'destroy']);
    Route::get('/purchase/{id}/{action}', [PurchaseController::class, 'purchaseProcessing']);
    Route::get('/purchase-report', [PurchaseController::class, 'purchaseReports']);

    // Delivery note routes
    Route::get('/delivery-note', [DeliveryNoteController::class, 'index']);
    Route::get('/delivery-note-code', [DeliveryNoteController::class, 'showNextCode']);
    Route::get('/delivery-note-order-select/{id}', [DeliveryNoteController::class, 'datasOnSelectOrder']);
    Route::post('/delivery-note', [DeliveryNoteController::class, 'store']);
    Route::get('/delivery-note/{id}/show', [DeliveryNoteController::class, 'show']);
    Route::get('/delivery-note/{id}/edit', [DeliveryNoteController::class, 'edit']);
    Route::patch('/delivery-note/{id}/update', [DeliveryNoteController::class, 'update']);
    Route::delete('/delivery-note/{id}/destroy', [DeliveryNoteController::class, 'destroy']);
    Route::get('/delivery-note/{id}/{action}', [DeliveryNoteController::class, 'deliveryNoteProcessing']);
    Route::get('/delivery-note/{id}/return', [DeliveryNoteController::class, 'returnOfMerchandises']);
    Route::get('/delivery-note-report', [DeliveryNoteController::class, 'deliveryNoteReports']);

    // Purchase order routes
    Route::get('/purchase-order', [PurchaseOrderController::class, 'index']);
    Route::get('/purchase-order-code', [PurchaseOrderController::class, 'showNextCode']);
    Route::get('/purchase-order-products-of-category/{id}', [PurchaseOrderController::class, 'productsOfSelectedCategory']);
    Route::post('/purchase-order', [PurchaseOrderController::class, 'store']);
    Route::get('/purchase-order/{id}/show', [PurchaseOrderController::class, 'show']);
    Route::get('/purchase-order/{id}/edit', [PurchaseOrderController::class, 'edit']);
    Route::patch('/purchase-order/{id}/update', [PurchaseOrderController::class, 'update']);
    Route::delete('/purchase-order/{id}/destroy', [PurchaseOrderController::class, 'destroy']);
    Route::get('/purchase-order/{id}/{action}', [PurchaseOrderController::class, 'purchaseOrderProcessing']);
    // Route::get('/purchase-order/{id}/validate', [PurchaseOrderController::class, 'validatePurchaseOrder'])->name('validate_purchase_order');
    // Route::get('/purchase-order/{id}/reject', [PurchaseOrderController::class, 'rejectPurchaseOrder'])->name('reject_purchase_order');
    Route::get('/purchase-order-report', [PurchaseOrderController::class, 'purchaseOrderReports']);

    // Sale routes
    Route::get('/sale-on-purchase-order', [SaleController::class, 'saleOnPurchaseOrder']);
    Route::get('/sale-on-purchase-order/{id}', [SaleController::class, 'datasFromPurchaseOrder']);
    Route::get('/sale-direct', [SaleController::class, 'directSale']);
    Route::get('/sale-code', [SaleController::class, 'showNextCode']);
    Route::get('/sale-products-of-category/{id}', [SaleController::class, 'productsOfSelectedCategory']);
    Route::post('/sale', [SaleController::class, 'store']);
    Route::get('/sale/{id}/show', [SaleController::class, 'show']);
    Route::get('/sale/{id}/edit', [SaleController::class, 'edit']);
    Route::patch('/sale/{id}/update', [SaleController::class, 'update']);
    Route::delete('/sale/{id}/destroy', [SaleController::class, 'destroy']);
    Route::get('/sale/{id}/{action}', [SaleController::class, 'saleProcessing']);
    // Route::get('/sale/{id}/validate', [SaleController::class, 'validateSale'])->name('validate_sale');
    // Route::get('/sale/{id}/reject', [SaleController::class, 'rejectSale'])->name('reject_sale');
    Route::get('/sale-report', [SaleController::class, 'saleReports']);

    // Client delivery note routes
    Route::get('/client-delivery-note', [ClientDeliveryNoteController::class, 'index']);
    Route::get('/client-delivery-note-code', [ClientDeliveryNoteController::class, 'showNextCode']);
    Route::get('/client-delivery-note-purchase-order-select/{id}', [ClientDeliveryNoteController::class, 'datasOnSelectPurchaseOrder']);
    Route::post('/client-delivery-note', [ClientDeliveryNoteController::class, 'store']);
    Route::get('/client-delivery-note/{id}/show', [ClientDeliveryNoteController::class, 'show']);
    Route::get('/client-delivery-note/{id}/edit', [ClientDeliveryNoteController::class, 'edit']);
    Route::patch('/client-delivery-note/{id}/update', [ClientDeliveryNoteController::class, 'update']);
    Route::delete('/client-delivery-note/{id}/destroy', [ClientDeliveryNoteController::class, 'destroy']);
    Route::get('/client-delivery-note/{id}/{action}', [ClientDeliveryNoteController::class, 'clientDeliveryNoteProcessing']);
    // Route::get('/client-delivery-note/{id}/validate', [ClientDeliveryNoteController::class, 'validateClientDeliveryNote'])->name('validate_client_delivery_note');
    // Route::get('/client-delivery-note/{id}/reject', [ClientDeliveryNoteController::class, 'rejectClientDeliveryNote'])->name('reject_client_delivery_note');
    Route::get('/client-delivery-note/{id}/return', [ClientDeliveryNoteController::class, 'returnOfMerchandises']);
    Route::get('/client-delivery-note-report', [ClientDeliveryNoteController::class, 'clientDeliveryNoteReports']);

    // Institution routes
    Route::get('/institution', [InstitutionController::class, 'index']);
    Route::post('/institution', [InstitutionController::class, 'store']);
    Route::get('/institution/{id}/show', [InstitutionController::class, 'show']);
    Route::get('/institution/{id}/edit', [InstitutionController::class, 'edit']);
    Route::patch('/institution/{id}/update', [InstitutionController::class, 'update']);
    Route::delete('/institution/{id}/destroy', [InstitutionController::class, 'destroy']);
    Route::get('/institution-report', [InstitutionController::class, 'institutionReports']);

    // Sale point routes
    Route::get('/sale-point', [SalePointController::class, 'index']);
    Route::post('/sale-point', [SalePointController::class, 'store']);
    Route::get('/sale-point/{id}/show', [SalePointController::class, 'show']);
    Route::get('/sale-point/{id}/edit', [SalePointController::class, 'edit']);
    Route::patch('/sale-point/{id}/update', [SalePointController::class, 'update']);
    Route::delete('/sale-point/{id}/destroy', [SalePointController::class, 'destroy']);
    Route::get('/sale-point-report', [SalePointController::class, 'salePointReports']);

    // Transfer demand routes
    Route::get('/transfer-demand', [TransferDemandController::class, 'index']);
    Route::get('/transfer-demand-code', [TransferDemandController::class, 'showNextCode']);
    Route::get('/transfer-demand-products-of-category/{id}', [TransferDemandController::class, 'productsOfSelectedCategory']);
    Route::get('/transfer-demand-on-transmitter-select', [TransferDemandController::class, 'showReceiversOnTransmitterSelect']);
    Route::post('/transfer-demand', [TransferDemandController::class, 'store']);
    Route::get('/transfer-demand/{id}/show', [TransferDemandController::class, 'show']);
    Route::get('/transfer-demand/{id}/edit', [TransferDemandController::class, 'edit']);
    Route::patch('/transfer-demand/{id}/update', [TransferDemandController::class, 'update']);
    Route::delete('/transfer-demand/{id}/destroy', [TransferDemandController::class, 'destroy']);
    Route::get('/transfer-demand-report', [TransferDemandController::class, 'transferDemandReports']);

    // Transfer demand processing routes
    Route::get('/transfer-demand-processing', [TransferDemandProcessingController::class, 'index']);
    Route::patch('/transfer-demand-processing/{id}/validate', [TransferDemandProcessingController::class, 'validateTransferDemand'])->name('validate.transfer_demand');
    Route::patch('/transfer-demand-processing/{id}/reject', [TransferDemandProcessingController::class, 'rejectTransferDemand'])->name('reject.transfer_demand');
    Route::patch('/transfer-demand-processing/{id}/transform-to-transfer', [TransferDemandProcessingController::class, 'transformDemandToTransfer']);

    // Transfer routes
    Route::get('/transfer', [TransferController::class, 'index']);
    Route::get('/transfer-code', [TransferController::class, 'showNextCode']);
    Route::get('/transfer-transfer-demand-select/{id}', [TransferController::class, 'datasOnSelectTransferDemand']);
    Route::post('/transfer', [TransferController::class, 'store']);
    Route::get('/transfer/{id}/show', [TransferController::class, 'show']);
    Route::get('/transfer/{id}/edit', [TransferController::class, 'edit']);
    Route::patch('/transfer/{id}/update', [TransferController::class, 'update']);
    Route::delete('/transfer/{id}/destroy', [TransferController::class, 'destroy']);
    Route::get('/transfer-report', [TransferController::class, 'transferReports']);

    // Compartment routes
    Route::get('/compartment', [CompartmentController::class, 'index']);
    Route::post('/compartment', [CompartmentController::class, 'store']);
    // Route::post('/compartment/{compartments}/{tank}', [CompartmentController::class, 'associateCompartmentsToTank']);
    Route::post('/associate-compartment', [CompartmentController::class, 'associateCompartmentsToTank']);
    Route::get('/compartment/{id}/show', [CompartmentController::class, 'show']);
    Route::get('/compartment/{id}/edit', [CompartmentController::class, 'edit']);
    Route::patch('/compartment/{id}/update', [CompartmentController::class, 'update']);
    Route::delete('/compartment/{id}/destroy', [CompartmentController::class, 'destroy']);
    Route::get('/compartment-report', [CompartmentController::class, 'compartmentReports']);

    // Tank routes
    Route::get('/tank', [TankController::class, 'index']);
    Route::post('/tank', [TankController::class, 'store']);
    Route::get('/tank/{id}/show', [TankController::class, 'show']);
    Route::get('/tank/{id}/edit', [TankController::class, 'edit']);
    Route::patch('/tank/{id}/update', [TankController::class, 'update']);
    Route::delete('/tank/{id}/destroy', [TankController::class, 'destroy']);
    Route::get('/tank-report', [TankController::class, 'tankReports']);

    // Truck routes
    Route::get('/truck', [TruckController::class, 'index']);
    Route::post('/truck', [TruckController::class, 'store']);
    Route::get('/truck/{id}/edit', [TruckController::class, 'edit']);
    Route::get('/truck/{id}/show', [TruckController::class, 'show']);
    Route::patch('/truck/{id}/update', [TruckController::class, 'update']);
    Route::delete('/truck/{id}/destroy', [TruckController::class, 'destroy']);
    Route::get('/truck-report', [TruckController::class, 'truckReports']);

    // Tank truck routes
    Route::get('/tank-truck/{param}/{id}', [TankTruckController::class, 'index']);
    Route::post('/tank-truck', [TankTruckController::class, 'store']);
    Route::get('/tank-truck/{id}/show', [TankTruckController::class, 'show']);
    Route::get('/tank-truck/{id}/edit', [TankTruckController::class, 'edit']);
    Route::patch('/tank-truck/{id}/update', [TankTruckController::class, 'update']);
    Route::delete('/tank-truck/{id}/destroy', [TankTruckController::class, 'destroy']);

    // Tourn routes
    Route::get('/tourn', [TournController::class, 'index']);
    Route::get('/tourn-code', [TournController::class, 'showNextCode']);
    Route::post('/tourn', [TournController::class, 'store']);
    Route::get('/tourn/{id}/show', [TournController::class, 'show']);
    Route::patch('/tourn/{id}/update', [TournController::class, 'update']);
    Route::delete('/tourn/{id}/destroy', [TournController::class, 'destroy']);
    Route::get('/tourn-report', [TournController::class, 'tournReports']);

    // Destination routes
    Route::get('/destination', [DestinationController::class, 'index']);
    Route::post('/destination', [DestinationController::class, 'store']);
    Route::get('/destination/{id}/show', [DestinationController::class, 'show']);
    Route::get('/destination/{id}/edit', [DestinationController::class, 'edit']);
    Route::patch('/destination/{id}/update', [DestinationController::class, 'update']);
    Route::delete('/destination/{id}/destroy', [DestinationController::class, 'destroy']);
    Route::get('/destination-report', [DestinationController::class, 'destinationReports']);

    // Good to remove routes
    Route::get('/removal-order', [RemovalOrderController::class, 'index']);
    Route::get('/removal-order-code', [RemovalOrderController::class, 'showNextCode']);
    Route::get('/removal-order/{id}/on-purchase-order-select', [RemovalOrderController::class, 'datasOnPurchaseOrderSelect']);
    Route::get('/removal-order/{id}/on-client-select', [RemovalOrderController::class, 'onClientSelect']);
    Route::get('/removal-order/{id}/on-select', [RemovalOrderController::class, 'onCarrierSelect']);
    Route::post('/removal-order', [RemovalOrderController::class, 'store']);
    Route::get('/removal-order/{id}/show', [RemovalOrderController::class, 'show']);
    Route::patch('/removal-order/{id}/update', [RemovalOrderController::class, 'update']);
    Route::delete('/removal-order/{id}/destroy', [RemovalOrderController::class, 'destroy']);

    // Extension routes
    Route::get('/extension', [ExtensionController::class, 'index']);
    Route::post('/extension', [ExtensionController::class, 'store']);
    Route::get('/extension/{id}/show', [ExtensionController::class, 'show']);
    Route::patch('/extension/{id}/update', [ExtensionController::class, 'update']);
    Route::delete('/extension/{id}/destroy', [ExtensionController::class, 'destroy']);
    Route::get('/extension-report', [ExtensionController::class, 'extensionReports']);

    // FileType routes
    Route::get('/file-type', [FileTypeController::class, 'index']);
    Route::post('/file-type', [FileTypeController::class, 'store']);
    Route::get('/file-type/{id}/show', [FileTypeController::class, 'show']);
    Route::patch('/file-type/{id}/update', [FileTypeController::class, 'update']);
    Route::delete('/file-type/{id}/destroy', [FileTypeController::class, 'destroy']);
    Route::get('/file-type-report', [FileTypeController::class, 'fileTypeReports']);

    // Folder routes
    Route::get('/folder', [FolderController::class, 'index']);
    Route::post('/folder', [FolderController::class, 'store']);
    Route::get('/folder/{id}/show', [FolderController::class, 'show']);
    Route::patch('/folder/{id}/update', [FolderController::class, 'update']);
    Route::delete('/folder/{id}/destroy', [FolderController::class, 'destroy']);
    Route::get('/folder-report', [FolderController::class, 'folderReports']);

    // PhoneOperator routes
    Route::get('/phone-operator', [PhoneOperatorController::class, 'index']);
    Route::post('/phone-operator', [PhoneOperatorController::class, 'store']);
    Route::get('/phone-operator/{id}/show', [PhoneOperatorController::class, 'show']);
    Route::patch('/phone-operator/{id}/update', [PhoneOperatorController::class, 'update']);
    Route::delete('/phone-operator/{id}/destroy', [PhoneOperatorController::class, 'destroy']);
    Route::get('/phone-operator-report', [PhoneOperatorController::class, 'phoneOperatorReports']);

    // DeliveryPoint routes
    Route::get('/delivery-point', [DeliveryPointController::class, 'index']);
    Route::post('/delivery-point', [DeliveryPointController::class, 'store']);
    Route::get('/delivery-point/{id}/show', [DeliveryPointController::class, 'show']);
    Route::patch('/delivery-point/{id}/update', [DeliveryPointController::class, 'update']);
    Route::delete('/delivery-point/{id}/destroy', [DeliveryPointController::class, 'destroy']);
    Route::get('/delivery-point-report', [DeliveryPointController::class, 'deliveryPointReports']);

    // Driver routes
    Route::get('/driver', [DriverController::class, 'index']);
    Route::get('/driver/{id}/hosts', [DriverController::class, 'hostsOfDriver']);
    Route::post('/driver', [DriverController::class, 'store']);
    Route::get('/driver/{id}/show', [DriverController::class, 'show']);
    Route::patch('/driver/{id}/update', [DriverController::class, 'update']);
    Route::delete('/driver/{id}/destroy', [DriverController::class, 'destroy']);
    Route::get('/driver-report', [DriverController::class, 'driverReports']);

    // Host routes
    Route::get('/host', [HostController::class, 'index']);
    Route::post('/host', [HostController::class, 'store']);
    Route::get('/host/{id}/show', [HostController::class, 'show']);
    Route::patch('/host/{id}/update', [HostController::class, 'update']);
    Route::delete('/host/{id}/destroy', [HostController::class, 'destroy']);
    Route::get('/host-report', [HostController::class, 'hostReports']);

    // Email channel param routes
    Route::get('/email-channel-param', [EmailChannelParamController::class, 'index']);
    Route::post('/email-channel-param', [EmailChannelParamController::class, 'store']);
    Route::get('/email-channel-param/{id}/show', [EmailChannelParamController::class, 'show']);
    Route::patch('/email-channel-param/{id}/update', [EmailChannelParamController::class, 'update']);
    Route::delete('/email-channel-param/{id}/destroy', [EmailChannelParamController::class, 'destroy']);
    Route::get('/email-channel-param-report', [EmailChannelParamController::class, 'emailChannelParamReports']);

    // Sms channel param routes
    Route::get('/sms-channel-param', [SmsChannelParamController::class, 'index']);
    Route::post('/sms-channel-param', [SmsChannelParamController::class, 'store']);
    Route::get('/sms-channel-param/{id}/show', [SmsChannelParamController::class, 'show']);
    Route::patch('/sms-channel-param/{id}/update', [SmsChannelParamController::class, 'update']);
    Route::delete('/sms-channel-param/{id}/destroy', [SmsChannelParamController::class, 'destroy']);
    Route::get('/sms-channel-param-report', [SmsChannelParamController::class, 'emailChannelParamReports']);

    // Taxe routes
    Route::get('/taxe', [TaxeController::class, 'index']);
    Route::post('/taxe', [TaxeController::class, 'store']);
    Route::patch('/taxe/{id}/update', [TaxeController::class, 'update']);
    Route::delete('/taxe/{id}/destroy', [TaxeController::class, 'destroy']);
    Route::get('/taxe/{id}/show', [TaxeController::class, 'show']);
    Route::get('/taxe-report', [TaxeController::class, 'taxeReports']);

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
        Route::get('/user-type-report', [UserTypeController::class, 'userTypeReports']);

        // Employee function routes
        // Route::get('/employee-function', [EmployeeFunctionController::class, 'index']);
        // Route::post('/employee-function', [EmployeeFunctionController::class, 'store']);
        // Route::get('/employee-function/{id}/show', [EmployeeFunctionController::class, 'show']);
        // Route::patch('/employee-function/{id}/update', [EmployeeFunctionController::class, 'update']);
        // Route::delete('/employee-function/{id}/destroy', [EmployeeFunctionController::class, 'destroy']);

        // Role routes
        // Route::get('/role', [RoleController::class, 'index']);
        // Route::post('/role', [RoleController::class, 'store']);
        // Route::get('/role/{id}/show', [RoleController::class, 'show']);
        // Route::patch('/role/{id}/update', [RoleController::class, 'update']);
        // Route::delete('/role/{id}/destroy', [RoleController::class, 'destroy']);

        // User routes
        Route::get('', [UserController::class, 'index']);
        Route::post('', [UserController::class, 'store']);
        Route::get('{id}/edit', [UserController::class, 'edit']);
        Route::patch('{id}/update', [UserController::class, 'update']);
        Route::delete('{id}/destroy', [UserController::class, 'destroy']);
        Route::patch('{id}/user-type-config', [UserController::class, 'userTypeConfiguration']);
        Route::patch('{id}/roles-config', [UserController::class, 'rolesConfiguration']);
        Route::patch('{id}/sale-points-config', [UserController::class, 'salePointsConfiguration']);
        Route::get('/user-report', [UserController::class, 'userReports']);

        // User profile routes
        Route::get('/profile', [UserProfileController::class, 'index']);
    });
});
