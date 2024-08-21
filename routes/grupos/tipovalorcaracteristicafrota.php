<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TipoValorCaracteristicaFrotaController;

Route::prefix('tipovalorcaracteristicafrota')->name('tipo_valor_caracteristica_frota.')->group(function () {
    Route::post('setperpage', [TipoValorCaracteristicaFrotaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [TipoValorCaracteristicaFrotaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [TipoValorCaracteristicaFrotaController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [TipoValorCaracteristicaFrotaController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [TipoValorCaracteristicaFrotaController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('tipovalorcaracteristicafrota', TipoValorCaracteristicaFrotaController::class)->names('tipo_valor_caracteristica_frota');
