<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\AiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — insend-app
|--------------------------------------------------------------------------
|
| Auth: Laravel Sanctum (Bearer Token)
| Base URL: /api/...
|
*/

// ─────────────────────────────────────────
// Public Routes (tidak perlu token)
// ─────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Produk & Resep bisa dilihat publik (tanpa login)
Route::get('/products',         [ProductController::class, 'index']);
Route::get('/products/{id}',    [ProductController::class, 'show']);
Route::get('/recipes',          [RecipeController::class, 'index']);
Route::get('/recipes/{id}',     [RecipeController::class, 'show']);

// ─────────────────────────────────────────
// Protected Routes (butuh Bearer Token)
// ─────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });

    // Users (admin: index & destroy | all: show & update diri sendiri)
    Route::prefix('users')->group(function () {
        Route::get('/',          [UserController::class, 'index']);
        Route::get('/{id}',      [UserController::class, 'show']);
        Route::put('/{id}',      [UserController::class, 'update']);
        Route::delete('/{id}',   [UserController::class, 'destroy']);
    });

    // User profile (shortcut untuk user sendiri)
    Route::get('/profile',  fn (\Illuminate\Http\Request $req) => response()->json(['data' => new \App\Http\Resources\UserResource($req->user())]));
    Route::put('/profile',  [UserController::class, 'update']);

    // Products (admin: store, update, destroy | all: sudah bisa di publik)
    Route::post('/products',          [ProductController::class, 'store']);
    Route::put('/products/{id}',      [ProductController::class, 'update']);
    Route::delete('/products/{id}',   [ProductController::class, 'destroy']);

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/',          [OrderController::class, 'index']);
        Route::post('/',         [OrderController::class, 'store']);
        Route::get('/{id}',      [OrderController::class, 'show']);
        Route::put('/{id}',      [OrderController::class, 'update']);
        Route::delete('/{id}',   [OrderController::class, 'destroy']);
    });

    // Recipes (protected: create, update, delete)
    Route::post('/recipes',          [RecipeController::class, 'store']);
    Route::put('/recipes/{id}',      [RecipeController::class, 'update']);
    Route::delete('/recipes/{id}',   [RecipeController::class, 'destroy']);

    // Insend AI
    Route::prefix('ai')->group(function () {
        Route::post('/chat',                       [AiController::class, 'chat']);
        Route::post('/generate-recipe',            [AiController::class, 'generateRecipe']);
        Route::get('/conversations',               [AiController::class, 'conversations']);
        Route::get('/conversations/{id}',          [AiController::class, 'showConversation']);
        Route::delete('/conversations/{id}',       [AiController::class, 'deleteConversation']);
    });
});
