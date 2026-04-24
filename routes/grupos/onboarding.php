<?php

use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;

// Fluxo de onboarding — disponível para qualquer usuário autenticado.
Route::prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/empresa',             [OnboardingController::class, 'empresa'])->name('empresa');
    Route::post('/empresa',            [OnboardingController::class, 'salvarEmpresa'])->name('empresa.salvar');
    Route::get('/plano',               [OnboardingController::class, 'plano'])->name('plano');
    Route::get('/plano/pagamento',     [OnboardingController::class, 'pagamento'])->name('plano.pagamento');
    Route::post('/plano/confirmar',    [OnboardingController::class, 'confirmarPlano'])->name('plano.confirmar');
    Route::get('/downgrade',           [OnboardingController::class, 'downgrade'])->name('downgrade');
    Route::post('/downgrade',          [OnboardingController::class, 'confirmarDowngrade'])->name('downgrade.confirmar');
    Route::get('/orientacoes',         [OnboardingController::class, 'orientacoes'])->name('orientacoes');
    Route::post('/concluir',           [OnboardingController::class, 'concluir'])->name('concluir');
});
