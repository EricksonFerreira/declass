<?php

use App\Http\Controllers\VeiculoDanoMonitoramentoController;
use Illuminate\Support\Facades\Route;

Route::prefix('veiculo-dano')->name('veiculo-dano.')->group(function () {
    Route::get('monitoramento', [VeiculoDanoMonitoramentoController::class, 'index'])->name('monitoramento');
    Route::post('{id}/correcao', [VeiculoDanoMonitoramentoController::class, 'registrarCorrecao'])->name('correcao');
});
