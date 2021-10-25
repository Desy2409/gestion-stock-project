<?php

use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\JuridicPersonalityController;
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
// Provider routes
Route::get('/providers', [ProviderController::class, 'index']);

// Category routes
Route::get('/category', [CategoryController::class, 'index']);
Route::post('/category', [CategoryController::class, 'store']);
Route::patch('/category/{id}/update', [CategoryController::class, 'update']);
Route::delete('/category/{id}/destroy', [CategoryController::class, 'destroy']);
Route::get('/category/{id}/show', [CategoryController::class, 'show']);

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

// Client routes
Route::get('/client', [ClientController::class, 'index']);
Route::post('/client', [ClientController::class, 'store']);
Route::get('/client/{id}/show', [ClientController::class, 'show']);
Route::patch('/client/{id}/update', [ClientController::class, 'update']);
Route::delete('/client/{id}/destroy', [ClientController::class, 'destroy']);

// Provider routes
Route::get('/provider', [ProviderController::class, 'index']);
Route::post('/provider', [ProviderController::class, 'store']);
Route::get('/provider/{id}/show', [ProviderController::class, 'show']);
Route::get('/provider/{id}/update', [ProviderController::class, 'edit']);
Route::patch('/provider/{id}/update', [ProviderController::class, 'update']);
Route::delete('/provider/{id}/destroy', [ProviderController::class, 'destroy']);

// Product routes
Route::get('/product-new', [ProductController::class, 'create']);
Route::post('/product-new', [ProductController::class, 'store']);
Route::get('/product/{id}/show', [ProductController::class, 'show']);
Route::get('/product/{id}/update', [ProductController::class, 'edit']);
Route::patch('/product/{id}/update', [ProductController::class, 'update']);
Route::delete('/product/{id}/destroy', [ProductController::class, 'destroy']);

// Purchase order routes
Route::get('/purchase-order-new', [PurchaseOrderController::class, 'create']);
Route::post('/purchase-order-new', [PurchaseOrderController::class, 'store']);
Route::get('/purchase-order/{id}/show', [PurchaseOrderController::class, 'show']);
Route::get('/purchase-order/{id}/update', [PurchaseOrderController::class, 'edit']);
Route::patch('/purchase-order/{id}/update', [PurchaseOrderController::class, 'update']);
Route::delete('/purchase-order/{id}/destroy', [PurchaseOrderController::class, 'destroy']);

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
