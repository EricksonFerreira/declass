<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModeloController;

Route::prefix('modelo')->name('modelo.')->group(function () {
    Route::get('getMarcaESegmento', [ModeloController::class, 'getMarcaESegmento'])
    ->name('getMarcaESegmento');

    Route::post('setperpage', [ModeloController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [ModeloController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [ModeloController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [ModeloController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [ModeloController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('modelo', ModeloController::class);
