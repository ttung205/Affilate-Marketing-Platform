<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\Publisher\AffiliateLinkController;
use App\Http\Controllers\Publisher\ProductController;
use App\Http\Controllers\Publisher\CampaignController;
use App\Http\Controllers\Publisher\WalletController;
use App\Http\Controllers\Publisher\WithdrawalController;
use App\Http\Controllers\Publisher\PaymentMethodController;
use App\Http\Controllers\Publisher\ProfileController;
use App\Http\Controllers\Publisher\RankingController;

// Publisher routes
Route::middleware(['auth', 'role:publisher'])->prefix('publisher')->name('publisher.')->group(function () {
    Route::get('/', [PublisherController::class, 'dashboard'])->name('dashboard');

    // Affiliate Links management routes
    Route::resource('affiliate-links', AffiliateLinkController::class);


    // Products routes
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/affiliate-link', [ProductController::class, 'createAffiliateLink'])->name('products.create-affiliate-link');

    // Campaigns routes
    Route::resource('campaigns', CampaignController::class);
    Route::post('/campaigns/{campaign}/affiliate-link', [CampaignController::class, 'createAffiliateLink'])->name('campaigns.create-affiliate-link');

    // Wallet routes
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/data', [WalletController::class, 'getWalletData'])->name('wallet.data');
    Route::post('/wallet/sync', [WalletController::class, 'syncWallet'])->name('wallet.sync');
    Route::post('/wallet/check-withdrawal', [WalletController::class, 'checkWithdrawal'])->name('wallet.check-withdrawal');
    Route::get('/wallet/stats', [WalletController::class, 'getStats'])->name('wallet.stats');
    Route::get('/wallet/transactions', [WalletController::class, 'getTransactions'])->name('wallet.transactions');
    Route::get('/wallet/earnings-chart', [WalletController::class, 'getEarningsChart'])->name('wallet.earnings-chart');

    // Withdrawal routes - rate limiting applied in controller for successful withdrawals only
    Route::post('/withdrawal', [WithdrawalController::class, 'store'])->name('withdrawal.store');

    // OTP resend with separate rate limiting
    Route::middleware(['throttle:withdrawal-otp'])->group(function () {
        Route::post('/withdrawal/otp/resend', [WithdrawalController::class, 'resendOTP'])->name('withdrawal.otp.resend');
    });

    Route::resource('withdrawal', WithdrawalController::class)->except(['store']);
    Route::post('/withdrawal/{withdrawal}/cancel', [WithdrawalController::class, 'cancel'])->name('withdrawal.cancel');
    Route::get('/withdrawal/api/list', [WithdrawalController::class, 'getWithdrawals'])->name('withdrawal.api.list');
    Route::get('/withdrawal/api/{withdrawal}', [WithdrawalController::class, 'getWithdrawal'])->name('withdrawal.api.show');
    Route::get('/withdrawal/api/stats', [WithdrawalController::class, 'getStats'])->name('withdrawal.api.stats');
    Route::post('/withdrawal/api/calculate-fee', [WithdrawalController::class, 'calculateFee'])->name('withdrawal.api.calculate-fee');

    // 2FA routes (mandatory for withdrawals)
    Route::get('/withdrawal/2fa/info', [WithdrawalController::class, 'get2FAInfo'])->name('withdrawal.2fa.info');

    // Payment Methods routes
    Route::resource('payment-methods', PaymentMethodController::class);
    Route::post('/payment-methods/{paymentMethod}/set-default', [PaymentMethodController::class, 'setDefault'])->name('payment-methods.set-default');
    Route::get('/payment-methods/api/list', [PaymentMethodController::class, 'getPaymentMethods'])->name('payment-methods.api.list');
    Route::get('/payment-methods/api/default', [PaymentMethodController::class, 'getDefaultPaymentMethod'])->name('payment-methods.api.default');
    Route::post('/payment-methods/api/calculate-fee', [PaymentMethodController::class, 'calculateFee'])->name('payment-methods.api.calculate-fee');
    Route::get('/payment-methods/api/banks', [PaymentMethodController::class, 'getSupportedBanks'])->name('payment-methods.api.banks');


    // Profile routes
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.remove-avatar');

    // Ranking routes
    Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');
    Route::get('/ranking/leaderboard', [RankingController::class, 'leaderboard'])->name('ranking.leaderboard');
    Route::get('/ranking/api/current', [RankingController::class, 'getCurrentRanking'])->name('ranking.api.current');
    Route::post('/ranking/api/update', [RankingController::class, 'updateRanking'])->name('ranking.update');
    Route::get('/ranking/api/leaderboard', [RankingController::class, 'getLeaderboard'])->name('ranking.api.leaderboard');
});
