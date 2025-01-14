<?php

use Mmrtonmoybd\Sslcommerz\Http\Controllers\SslCommerzPaymentController;

// SSLCOMMERZ Start

//Route::post('sslcommerz/ipn', [SslCommerzPaymentController::class, 'ipn'])->name('sslcommerz.ipn'); Feature Request

Route::group(['middleware' => ['web']], function () {
    Route::prefix('sslcommerz')->group(function () {
        Route::get('/sslcommerz-redirect', [SslCommerzPaymentController::class, 'index'])->name('sslcommerz.process');

        Route::post('/success', [SslCommerzPaymentController::class, 'success'])->name('sslcommerz.success')->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        Route::post('/fail', [SslCommerzPaymentController::class, 'fail'])->name('sslcommerz.fail')->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn'])->name('sslcommerz.ipn')->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    });
});
//SSLCOMMERZ END
