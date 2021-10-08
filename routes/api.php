<?php

use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProviderController;
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
Route::get('/products/{id}/show', [ProductController::class, 'show']);
Route::get('/products/{$name}/search', [ProductController::class, 'show']);


// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthUserController::class, 'logout']);

    // Category routes
    Route::get('/category', [CategoryController::class, 'index']);
    Route::post('/category', [CategoryController::class, 'store']);
    Route::patch('/category/{id}/update', [CategoryController::class, 'update']);
    Route::delete('/category/{id}/destroy', [CategoryController::class, 'destroy']);

    // Sub category routes
    Route::get('/sub-category', [SubCategoryController::class, 'index']);
    Route::post('/sub-category', [SubCategoryController::class, 'store']);
    Route::patch('/sub-category/{id}/update', [SubCategoryController::class, 'update']);
    Route::delete('/sub-category/{id}/destroy', [SubCategoryController::class, 'destroy']);

    // Client routes
    Route::get('/client', [ClientController::class, 'index']);
    Route::get('/client-new', [ClientController::class, 'create']);
    Route::post('/client', [ClientController::class, 'store']);
    Route::get('/client{id}/show', [ClientController::class, 'show']);
    Route::patch('/client/{id}/update', [ClientController::class, 'update']);
    Route::delete('/client/{id}/destroy', [ClientController::class, 'destroy']);

    // Provider routes
    Route::get('/provider', [ProviderController::class, 'index']);
    Route::get('/provider-new', [ProviderController::class, 'create']);
    Route::post('/provider', [ProviderController::class, 'store']);
    Route::get('/provider{id}/show', [ProviderController::class, 'show']);
    Route::patch('/provider/{id}/update', [ProviderController::class, 'update']);
    Route::delete('/provider/{id}/destroy', [ProviderController::class, 'destroy']);

    // Product routes
    Route::post('/products', [ProductController::class, 'store']);
    Route::patch('/products/{id}/update', [ProductController::class, 'update']);
    Route::delete('/products/{id}/destroy', [ProductController::class, 'destroy']);
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
