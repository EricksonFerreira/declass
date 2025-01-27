<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DanoController;

Route::prefix('dano')->name('dano.')->group(function () {
    Route::post('setperpage', [DanoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [DanoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [DanoController::class, 'filtro'])->name('filtro');
    Route::get('obterDano',[DanoController::class,'obterDanosPorCategoriaELocalizacao'])->name('obterDanosPorCategoriaELocalizacao');
    Route::get('obterDanosExistentes', [DanoController::class, 'obterDanosExistentes'])->name('obterDanosExistentes');
    Route::get('obterDanosDetectados', [DanoController::class, 'obterDanosDetectados'])->name('obterDanosDetectados');
});
Route::resource('dano', DanoController::class);
