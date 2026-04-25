<?php

use App\Http\Controllers\ReplicacaoBaseController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:acessar-superadmin')->prefix('replicacao-base')->name('replicacao_base.')->group(function () {
    Route::get('/', [ReplicacaoBaseController::class, 'index'])->name('index');
    Route::post('/', [ReplicacaoBaseController::class, 'store'])->name('store');
});
