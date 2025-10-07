<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shop\DashboardController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Shop\ProfileController;
use App\Http\Controllers\Shop\ConversionController as ShopConversionController;
use App\Http\Controllers\Shop\PlatformFeePaymentController;

// Shop routes
Route::middleware(['auth', 'role:shop'])->prefix('shop')->name('shop.')->group(function () {
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

    //import-export excel
    Route::get('products/export-excel', [ProductController::class, 'exportExcel'])
        ->name('products.export-excel');

    Route::post('products/import-excel', [ProductController::class, 'importExcel'])
        ->name('products.import-excel');
    Route::post('products/preview-import', [ProductController::class, 'previewImport'])
        ->name('products.preview-import');

    Route::get('conversions', [ShopConversionController::class, 'index'])
        ->name('conversions.index');
    Route::patch('conversions/{conversion}', [ShopConversionController::class, 'updateStatus'])
        ->name('conversions.update-status');
    // Product management
    Route::resource('products', ProductController::class);
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
        ->name('products.toggle-status');
    Route::delete('/products/{product}/image', [ProductController::class, 'removeImage'])
        ->name('products.remove-image');

    // Profile routes
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.remove-avatar');

    // Platform Fee Payment routes
    Route::get('/platform-fee', [PlatformFeePaymentController::class, 'index'])->name('platform-fee.index');
    Route::post('/platform-fee/generate-qr', [PlatformFeePaymentController::class, 'generateQR'])->name('platform-fee.generate-qr');
    Route::post('/platform-fee/confirm', [PlatformFeePaymentController::class, 'confirmPayment'])->name('platform-fee.confirm');
});

