<?php

use App\Http\Controllers\OcorrenciaController;
use Illuminate\Support\Facades\Route;

Route::prefix('ocorrencia')->name('ocorrencia.')->group(function () {
    Route::post('setperpage', [OcorrenciaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [OcorrenciaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [OcorrenciaController::class, 'filtro'])->name('filtro');
});
Route::resource('ocorrencia', OcorrenciaController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('ocorrencia');
