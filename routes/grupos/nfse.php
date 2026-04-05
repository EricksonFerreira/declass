<?php

use App\Http\Controllers\NfseController;
use Illuminate\Support\Facades\Route;

Route::prefix('nfse')->name('nfse.')->group(function (): void {
    Route::get('/', [NfseController::class, 'index'])->name('index');
    Route::get('{id}/download', [NfseController::class, 'download'])->name('download');
    Route::post('importar-pendentes', [NfseController::class, 'importarPendentes'])->name('importarPendentes');
});
