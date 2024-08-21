<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarcaController;

Route::prefix('marca')->name('marca.')->group(function () {
    Route::post('setperpage', [MarcaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [MarcaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [MarcaController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [MarcaController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [MarcaController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('marca', MarcaController::class);