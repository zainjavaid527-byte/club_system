<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerSessionController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SessionItemController;
use App\Http\Controllers\PaymentController;


/*
|--------------------------------------------------------------------------
| Auth APIs (public — no middleware, this is how a token is obtained)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| Everything below requires a valid Sanctum token
| (Authorization: Bearer <token>)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Auth-related, but needs to know WHO is logged in -> protected
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/me', [AuthController::class, 'me']);

    /*
    |--------------------------------------------------------------------------
    | Customer APIs
    |--------------------------------------------------------------------------
    */

    Route::prefix('customers')->group(function () {

        Route::get('/', [CustomerController::class, 'index']);

        Route::post('/', [CustomerController::class, 'store']);

        Route::get('/{customer}', [CustomerController::class, 'show']);

        Route::put('/{customer}', [CustomerController::class, 'update']);

        Route::delete('/{customer}', [CustomerController::class, 'destroy']);
    });


    /*
    |--------------------------------------------------------------------------
    | Customer Session APIs
    |--------------------------------------------------------------------------
    */

    Route::prefix('sessions')->group(function () {

        // Start new customer session
        Route::post('/', [CustomerSessionController::class, 'store']);

        // Udhaar list — must come BEFORE /{customerSession} or Laravel
        // will try to treat "dues" as a session ID and fail.
        Route::get('/dues', [CustomerSessionController::class, 'dues']);

        // Get complete session / running bill
        Route::get('/{customerSession}', [CustomerSessionController::class, 'show']);

        // Close session
        Route::post('/{customerSession}/close', [CustomerSessionController::class, 'close']);
    });


    /*
    |--------------------------------------------------------------------------
    | Game APIs
    |--------------------------------------------------------------------------
    */

    Route::prefix('games')->group(function () {

        // Add game to customer session
        Route::post('/', [GameController::class, 'store']);

        // Remove game
        Route::delete('/{game}', [GameController::class, 'destroy']);
    });


    /*
    |--------------------------------------------------------------------------
    | Product / Canteen APIs
    |--------------------------------------------------------------------------
    */

    Route::prefix('products')->group(function () {

        // Get all products
        Route::get('/', [ProductController::class, 'index']);

        // Create product
        Route::post('/', [ProductController::class, 'store']);

        // Update product
        Route::put('/{product}', [ProductController::class, 'update']);

        // Delete product
        Route::delete('/{product}', [ProductController::class, 'destroy']);
    });


    /*
    |--------------------------------------------------------------------------
    | Canteen Session Item APIs
    |--------------------------------------------------------------------------
    */

    Route::prefix('session-items')->group(function () {

        // Add canteen item to customer session
        Route::post('/', [SessionItemController::class, 'store']);

        // Remove canteen item
        Route::delete('/{sessionItem}', [SessionItemController::class, 'destroy']);
    });


    /*
    |--------------------------------------------------------------------------
    | Payment APIs
    |--------------------------------------------------------------------------
    */

    Route::prefix('payments')->group(function () {

        // Make payment
        Route::post('/', [PaymentController::class, 'store']);

        // Payment history of a session
        Route::get('/session/{customerSession}', [PaymentController::class, 'index']);
    });

});