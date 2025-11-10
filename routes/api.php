<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Controllers\Api\V1\TransactionController;

Route::prefix('v1')->group(function () {

    // Auth endpoints
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth.api');
    Route::post('auth/refresh', [AuthController::class, 'refresh'])->middleware('auth.api');

    // Compte endpoints
    Route::middleware('auth.api')->group(function () {
        Route::get('compte', [CompteController::class, 'show']);
    });

    // Transactions
    Route::middleware('auth.api')->group(function () {
        Route::post('transactions/depot', [TransactionController::class, 'depot']);
        Route::post('transactions/retrait', [TransactionController::class, 'retrait']);
        Route::post('transactions/paiement', [TransactionController::class, 'paiementMarchand']);
        Route::post('transactions/transfert', [TransactionController::class, 'transfert']);
    });
});

