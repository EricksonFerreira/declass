<?php

use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;

// Fluxo de onboarding — disponível para qualquer usuário autenticado.
Route::prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/empresa', [OnboardingController::class, 'empresa'])->name('empresa');
    Route::post('/empresa', [OnboardingController::class, 'salvarEmpresa'])->name('empresa.salvar');
    Route::get('/cores', [OnboardingController::class, 'cores'])->name('cores');
    Route::post('/cores', [OnboardingController::class, 'salvarCores'])->name('cores.salvar');
    Route::get('/orientacoes', [OnboardingController::class, 'orientacoes'])->name('orientacoes');
    Route::post('/concluir', [OnboardingController::class, 'concluir'])->name('concluir');
});
