<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TempoEntreEntacaoController;

Route::prefix('tempo_entre_entacao')->name('tempo_entre_entacao.')->group(function () {
    Route::post('setperpage', [TempoEntreEntacaoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [TempoEntreEntacaoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [TempoEntreEntacaoController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [TempoEntreEntacaoController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [TempoEntreEntacaoController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('tempo_entre_entacao', TempoEntreEntacaoController::class);