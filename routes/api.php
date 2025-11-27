<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Controllers\Api\V1\TransactionController;

Route::prefix('v1')->group(function () {

    // Auth endpoints - Nouveau flux téléphone + OTP
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('verify', [AuthController::class, 'verifyOtp']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('verify-login', [AuthController::class, 'verifyLoginOtp']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
        Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    });

    // Accounts endpoints - Nouveau système par numero_compte
    Route::middleware(['auth:api'])->group(function () {
        Route::get('compte', [CompteController::class, 'show']);
        Route::get('accounts/{numerocompte}', [AccountController::class, 'show']);
        Route::get('accounts/{numerocompte}/balance', [AccountController::class, 'balance']);
        Route::get('accounts/{numerocompte}/transactions', [AccountController::class, 'transactions']);

        // Transactions (maintenues pour compatibilité mais dépréciées)
        Route::get('transactions', [TransactionController::class, 'index']);
        Route::post('transactions/depot', [TransactionController::class, 'depot']);
        Route::post('transactions/retrait', [TransactionController::class, 'retrait']);
        Route::post('transactions/paiement', [TransactionController::class, 'paiementMarchand']);
        Route::post('transactions/transfert', [TransactionController::class, 'transfert']);
    });
});


