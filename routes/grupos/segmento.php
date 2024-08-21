<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SegmentoController;

Route::prefix('segmento')->name('segmento.')->group(function () {
    Route::post('setperpage', [SegmentoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [SegmentoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [SegmentoController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [SegmentoController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [SegmentoController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('segmento', SegmentoController::class);