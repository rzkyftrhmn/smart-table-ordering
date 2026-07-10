<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerMenuController;

Route::prefix('customer-menu/{token}')
    ->name('customer-menu.')
    ->group(function () {

        Route::get('/location', [CustomerMenuController::class, 'checkLocation'])
            ->name('check');

        Route::post('/location/verify', [CustomerMenuController::class, 'verifyLocation'])
            ->name('verify');

        Route::get('/', [CustomerMenuController::class, 'index'])
            ->name('index');

        Route::get('/search', [CustomerMenuController::class, 'search'])
            ->name('search');

        Route::get('/cart', [CustomerMenuController::class, 'cart'])
            ->name('cart');

        Route::post('/cart/add', [CustomerMenuController::class, 'addToCart'])
            ->name('cart.add');

        Route::post('/cart/update', [CustomerMenuController::class, 'updateCart'])
            ->name('cart.update');

        Route::post('/cart/remove', [CustomerMenuController::class, 'removeFromCart'])
            ->name('cart.remove');

        Route::post('/checkout', [CustomerMenuController::class, 'checkout'])
            ->name('checkout');
    });
