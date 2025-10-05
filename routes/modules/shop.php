<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shop\DashboardController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Shop\ProfileController;

// Shop routes
Route::middleware(['auth', 'role:shop'])->prefix('shop')->name('shop.')->group(function () {
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

    //import-export excel
    Route::get('products/export-excel', [ProductController::class, 'exportExcel'])
        ->name('products.export-excel');

    Route::post('products/import-excel', [ProductController::class, 'importExcel'])
        ->name('products.import-excel');
    
// routes/web.php
Route::post('products/preview-import', [ProductController::class, 'previewImport'])->name('products.preview-import');
Route::post('shop/products/import-excel', [ProductController::class, 'importExcel'])
    ->name('shop.products.import-excel');




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
});

