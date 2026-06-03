<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\PromoController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\BlogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — InSend App
|--------------------------------------------------------------------------
|
| Auth: Laravel Sanctum (Bearer Token)
| Base URL: /api/...
| Response format: { status, message, data, meta }
|
*/

// ═════════════════════════════════════════════════════════════
// PUBLIC ROUTES (tidak perlu token)
// ═════════════════════════════════════════════════════════════

// ── Auth (rate-limited: 5 req/minute per IP) ────────────────
Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
    Route::post('/register',        [AuthController::class, 'register']);
    Route::post('/login',           [AuthController::class, 'login']);
    Route::post('/social-login',    [AuthController::class, 'socialLogin']);
    Route::post('/verify-email',    [AuthController::class, 'verifyEmail']);
    Route::post('/resend-otp',      [AuthController::class, 'resendOtp']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',  [AuthController::class, 'resetPassword']);
});

// ── Products (public read) ──────────────────────────────────
Route::get('/products',         [ProductController::class, 'index']);
Route::get('/products/{id}',    [ProductController::class, 'show']);

// ── Categories (public read) ────────────────────────────────
Route::get('/categories',          [CategoryController::class, 'index']);
Route::get('/categories/{slug}',   [CategoryController::class, 'show']);

// ── Recipes (public read) ───────────────────────────────────
Route::get('/recipes',          [RecipeController::class, 'index']);
Route::get('/recipes/{id}',     [RecipeController::class, 'show']);

// ── Reviews (public read) ───────────────────────────────────
// ── Reviews (public read) ───────────────────────────────────
Route::get('/reviews/featured', [ReviewController::class, 'featured']);
Route::get('/products/{product_id}/reviews', [ReviewController::class, 'productReviews']);

// ── Blog (public read) ──────────────────────────────────────
Route::get('/blog/sidebar', [BlogController::class, 'sidebar']);
Route::get('/blog', [BlogController::class, 'index']);
Route::get('/blog/{id}', [BlogController::class, 'show']);

// ── Promos (public list) ────────────────────────────────────
Route::get('/promos', [PromoController::class, 'index']);

// ── Delivery zones (public) ─────────────────────────────────
Route::get('/delivery/zones', [DeliveryController::class, 'zones']);
Route::get('/delivery/slots', [DeliveryController::class, 'slots']);

// ── Payment Webhook (Midtrans — server-to-server, NO AUTH) ──
Route::post('/payment/callback', [PaymentController::class, 'callback']);


// ═════════════════════════════════════════════════════════════
// PROTECTED ROUTES (butuh Bearer Token)
// ═════════════════════════════════════════════════════════════
Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });

    // ── User Profile ────────────────────────────────────────
    Route::get('/profile', fn (\Illuminate\Http\Request $req) => response()->json([
        'status' => 'success',
        'data'   => new \App\Http\Resources\UserResource($req->user()),
    ]));
    Route::put('/profile', [UserController::class, 'update']);

    // ── Users (admin: index & destroy | all: show & update) ─
    Route::prefix('users')->group(function () {
        Route::get('/',          [UserController::class, 'index']);
        Route::get('/{id}',      [UserController::class, 'show']);
        Route::put('/{id}',      [UserController::class, 'update']);
        Route::delete('/{id}',   [UserController::class, 'destroy']);
    });

    // ── Products (admin: store, update, destroy) ────────────
    Route::post('/products',          [ProductController::class, 'store']);
    Route::put('/products/{id}',      [ProductController::class, 'update']);
    Route::delete('/products/{id}',   [ProductController::class, 'destroy']);

    // ── Categories (admin: store, update, destroy) ──────────
    Route::post('/categories',          [CategoryController::class, 'store']);
    Route::put('/categories/{id}',      [CategoryController::class, 'update']);
    Route::delete('/categories/{id}',   [CategoryController::class, 'destroy']);

    // ── Cart ────────────────────────────────────────────────
    Route::prefix('cart')->group(function () {
        Route::get('/',                     [CartController::class, 'index']);
        Route::post('/items',               [CartController::class, 'addItem']);
        Route::patch('/items/{product_id}', [CartController::class, 'updateItem']);
        Route::delete('/items/{product_id}',[CartController::class, 'removeItem']);
        Route::delete('/',                  [CartController::class, 'clear']);
        Route::post('/checkout',            [CartController::class, 'checkout']);
    });

    // ── Addresses ───────────────────────────────────────────
    Route::prefix('addresses')->group(function () {
        Route::get('/',                 [AddressController::class, 'index']);
        Route::post('/',                [AddressController::class, 'store']);
        Route::get('/{id}',             [AddressController::class, 'show']);
        Route::put('/{id}',             [AddressController::class, 'update']);
        Route::delete('/{id}',          [AddressController::class, 'destroy']);
        Route::patch('/{id}/default',   [AddressController::class, 'setDefault']);
    });

    // ── Orders ──────────────────────────────────────────────
    Route::prefix('orders')->group(function () {
        Route::get('/',          [OrderController::class, 'index']);
        Route::post('/',         [OrderController::class, 'store']);
        Route::get('/{id}',      [OrderController::class, 'show']);
        Route::put('/{id}',      [OrderController::class, 'update']);
        Route::delete('/{id}',   [OrderController::class, 'destroy']);

        // Payment
        Route::get('/{id}/payment-status',  [PaymentController::class, 'paymentStatus']);
        Route::post('/{id}/pay',            [PaymentController::class, 'pay']);

        // Review
        Route::post('/{id}/review',         [ReviewController::class, 'store']);
    });

    // ── Delivery ────────────────────────────────────────────
    Route::post('/delivery/calculate', [DeliveryController::class, 'calculate']);

    // ── Wishlist ────────────────────────────────────────────
    Route::prefix('wishlist')->group(function () {
        Route::get('/',                     [WishlistController::class, 'index']);
        Route::post('/',                    [WishlistController::class, 'store']);
        Route::delete('/{product_id}',      [WishlistController::class, 'destroy']);
        Route::get('/check/{product_id}',   [WishlistController::class, 'check']);
    });

    // ── Promos ──────────────────────────────────────────────
    Route::post('/promos/validate',   [PromoController::class, 'validateCode']);
    Route::post('/promos',            [PromoController::class, 'store']);
    Route::put('/promos/{id}',        [PromoController::class, 'update']);
    Route::delete('/promos/{id}',     [PromoController::class, 'destroy']);

    // ── Reviews ─────────────────────────────────────────────
    Route::get('/reviews/my', [ReviewController::class, 'myReviews']);

    // ── Recipes (protected: create, update, delete) ─────────
    Route::post('/recipes',          [RecipeController::class, 'store']);
    Route::put('/recipes/{id}',      [RecipeController::class, 'update']);
    Route::delete('/recipes/{id}',   [RecipeController::class, 'destroy']);

    // ── AI Chat (rate-limited: 20 req/minute per user) ─────────
    Route::prefix('ai')->middleware('throttle:20,1')->group(function () {
        Route::post('/chat',                       [AiController::class, 'chat']);
        Route::post('/generate-recipe',            [AiController::class, 'generateRecipe']);
        Route::get('/conversations',               [AiController::class, 'conversations']);
        Route::get('/conversations/{id}',          [AiController::class, 'showConversation']);
        Route::delete('/conversations/{id}',       [AiController::class, 'deleteConversation']);
    });

    // ── Admin: Inventory Management ─────────────────────────
    Route::prefix('admin/inventory')->group(function () {
        Route::get('/low-stock',        [InventoryController::class, 'lowStock']);
        Route::get('/summary',          [InventoryController::class, 'summary']);
        Route::patch('/{id}/stock',     [InventoryController::class, 'updateStock']);
    });
});
