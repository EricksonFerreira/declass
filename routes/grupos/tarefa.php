<?php

use App\Http\Controllers\TarefaController;
use Illuminate\Support\Facades\Route;

Route::prefix('tarefa')->name('tarefa.')->group(function () {
    Route::post('setperpage', [TarefaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [TarefaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [TarefaController::class, 'filtro'])->name('filtro');
    Route::get('{tarefa}/entregas', [TarefaController::class, 'entregas'])->name('entregas');
    Route::post('{tarefa}/entregas', [TarefaController::class, 'salvarEntregas'])->name('entregas.salvar');
});
Route::resource('tarefa', TarefaController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('tarefa');
